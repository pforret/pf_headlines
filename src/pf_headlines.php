<?php

namespace pforret\pf_headlines;

use voku\helper\StopWords;
use voku\helper\StopWordsLanguageNotExists;
use VRia\Utils\NoDiacritic;

Class pf_headlines {
    protected $stop_words=[];
    protected $locale="";
    protected $sentence_id=0;
    protected $sentence_list=[];
    protected $min_words=2;
    protected $max_words=4;
    protected $votes=[];
    protected $vote_count=[];

    /**
     * @param array $words
     * @return $this
     */
    public function remove_stopwords($words=[]){
        if($this->stop_words){
            if(is_string($words) AND $words="*"){
                $this->stop_words=[];
            } else {
                $words=$this->text_to_array($words);
                foreach($words as $word){
                    $word=trim(strtolower($word));
                    if(isset($this->stop_words[$word])){
                        unset($this->stop_words[$word]);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param string $locale
     * @return $this|bool
     */
    public function set_locale($locale="en"){
        $sw = new StopWords();
        try {
            $new_words = $sw->getStopWordsFromLanguage($locale);
        } catch (StopWordsLanguageNotExists $e) {
            return false;
        }
        $this->locale=$locale;
        if($new_words){
            $this->add_stopwords($new_words);
        }
        return $this;
    }

    /**
     * @param array $new_words
     * @return $this
     */
    public function add_stopwords($new_words=[]){
        $new_words=$this->text_to_array($new_words);
        foreach($new_words as $new_word){
            $new_word=trim(strtolower(NoDiacritic::filter($new_word)));
            $this->stop_words[$new_word]=$new_word;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function get_stopwords(){
        return $this->stop_words;
    }

    /**
     * @param $sentence
     * @return string
     */
    public function reduce_sentence($sentence){
        $remove_chars=[".",",",";",":","!","?","/","&"];

        $sentence=NoDiacritic::filter($sentence);
        $simple=" ".strtolower($sentence)." ";
        $simple=str_replace($remove_chars," ",$simple);
        foreach($this->stop_words as $word){
            $simple=str_replace(" $word "," ",$simple);
        }
        $simple=preg_replace("#\s\s+#"," ",$simple);
        return trim($simple);
    }

    public function add_sentence($sentence,$url=""){
        $this->sentence_id++;
        $simple=$this->reduce_sentence($sentence);
        $this->sentence_list[$this->sentence_id]=[
            "sentence"  =>  $sentence,
            "simple"    =>  $simple,
            "url"       =>  $url,
        ];
        $word_list=explode(" ",$simple);
        for($count_words=$this->min_words;$count_words<=$this->max_words;$count_words++){
            for($skip=0;$skip<=count($word_list)-$count_words;$skip++){
                $combination=array_slice($word_list,$skip,$count_words);
                $this->add_combination($combination,$this->sentence_id,($count_words-$this->min_words)/2 + 1);
            }
        }
    }

    public function get_top_votes($limit=10){
        $top_list=$this->vote_count;
        arsort($top_list);
        foreach($top_list as $combination => $count){
            if($count < 2)  unset($top_list[$combination]);
        }
        return array_slice($top_list,0, $limit);
    }

    public function get_votes($limit=10){
        $votes=$this->get_top_votes($limit);
        $results=[];
        $already_seen=[];
        foreach($votes as $combination => $count){
            $result=[];
            $result["count"]=$count;
            $result["votes"]=[];
            foreach($this->votes[$combination] as $sentence_id){
                if(!isset($already_seen[$sentence_id])){
                    $result["votes"][$sentence_id]=$this->sentence_list[$sentence_id];
                    $already_seen[$sentence_id]=$sentence_id;
                }
            }
            if($result["votes"]){
                $results[$combination]=$result;
            }
        }
        return $results;
    }

    public function get_all_votes(){
        return $this->votes;
    }

    //----
    protected function add_combination($combination,$id,$weight=1){
        sort($combination);
        $combination_string=implode(" ",$combination);
        if(isset($this->votes[$combination_string])){
            $this->vote_count[$combination_string]+=$weight;
        } else {
            $this->vote_count[$combination_string]=$weight;
        }
        $this->votes[$combination_string][]=$id;
    }

    /**
     * @param $text
     * @return array|string|string[]
     */
    protected function text_to_array($text){
        if(is_string($text)){
            $text=str_replace( [",",";",":"],"|", $text);
            return explode("|",$text);
        }
        return $text;
    }
}
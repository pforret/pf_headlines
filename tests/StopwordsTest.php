<?php

namespace Tests;

use pforret\pf_headlines\pf_headlines;

class StopwordsTest extends \PHPUnit\Framework\TestCase
{

    public function testLoadLanguageEN()
    {
        $hl=New pf_headlines();
        $hl->set_locale(); // load english
        $words=$hl->get_stopwords();
        //print_r($words);
        $this->assertArrayHasKey("am",$words);
        $this->assertArrayNotHasKey("This",$words);
        $this->assertArrayHasKey("this",$words);
        $hl->remove_stopwords("*");
        $words=$hl->get_stopwords();
        $this->assertArrayNotHasKey("am",$words);
    }

    public function testLoadLanguageFR()
    {
        $hl=New pf_headlines();
        $hl->set_locale("fr"); // load english
        $words=$hl->get_stopwords();
        //print_r($words);
        $this->assertArrayHasKey("trop",$words);
        $this->assertArrayNotHasKey("Trop",$words);
        $this->assertArrayHasKey("qui",$words);
        $hl->remove_stopwords("qui");
        $words=$hl->get_stopwords();
        $this->assertArrayNotHasKey("qui",$words);
    }

    public function testReduceSentenceEN()
    {
        $hl=New pf_headlines();
        $hl->set_locale(); // load english
        $hl->add_stopwords("I");
        $this->assertEquals($hl->reduce_sentence("The quick brown fox jumps over the two lazy dogs"),"quick brown fox jumps lazy dogs");
        $this->assertEquals($hl->reduce_sentence("You might literally be buying trash on Amazon"),"literally buying trash amazon");
        $this->assertEquals($hl->reduce_sentence("you are the most one of two"),"");
        $this->assertEquals($hl->reduce_sentence("you&I are the most one of two!"),"");
    }

    public function testReduceSentenceFR()
    {
        $hl=New pf_headlines();
        $hl->set_locale("fr"); // load english
        $this->assertEquals($hl->reduce_sentence("Je ne suis pas la pour te servir"),"servir");
        $this->assertEquals($hl->reduce_sentence("Grève : la SNCF assure que tous les voyageurs ayant un billet de TGV pour ce week-end pourront se déplacer"),"greve sncf assure voyageurs billet tgv week-end pourront deplacer");
        $this->assertEquals($hl->reduce_sentence("you are the most one of two"),"you are the most one of two");
    }

    public function testCombinationsEN()
    {
        $hl=New pf_headlines();
        $hl->set_locale(); // load english
        $hl->add_sentence("Google is the top advertising network today","https://www.google.com");
        $hl->add_sentence("Google is the top advertising network today","https://www.google.com");
        $hl->add_sentence("Facebook is no longer the best advertising network today","https://www.facebook.com");
        $hl->add_sentence("What is the best advertising network today?","https://www.whois.com");
        $hl->add_sentence("What network for advertising is best?","https://www.superior.com");
        $hl->add_sentence("Google announces big name change","https://www.google.com");
        $this->assertArrayHasKey("advertising network",$hl->get_top_votes());

        print_r($hl->get_votes());
    }



}

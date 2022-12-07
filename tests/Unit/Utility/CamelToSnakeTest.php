<?php

namespace Skywarth\LaravelConfigMapper\Tests\Unit\Utility;

use Skywarth\LaravelConfigMapper\Utility;

class CamelToSnakeTest extends \Skywarth\LaravelConfigMapper\Tests\TestCase
{

    public function test_empty_string()
    {
        $sample='';
        $expected='';
        $result=Utility::camelToSnake($sample);
        $this->assertEquals($expected,$result);
    }



    public function test_one_word_simple_string()
    {
        $sample='noice';
        $expected='noice';
        $result=Utility::camelToSnake($sample);
        $this->assertEquals($expected,$result);
    }

    public function test_one_word_special_character_string()
    {
        $sample='no!ce';
        $expected='no!ce';
        $result=Utility::camelToSnake($sample);
        $this->assertEquals($expected,$result);
    }

    public function test_two_word_simple_string()
    {
        $sample='noiceJob';
        $expected='noice_job';
        $result=Utility::camelToSnake($sample,'_');

        $this->assertEquals($expected,$result);
    }

    public function test_multi_word_simple_string()
    {
        $sample='noiceJobDude';
        $expected='noice_job_dude';
        $result=Utility::camelToSnake($sample,'_');

        $this->assertEquals($expected,$result);
    }

    public function test_multi_word_simple_string_with_unseperated_words()
    {
        $sample='brownFoxJumpsOverThelazyfox';
        $expected='brown_fox_jumps_over_thelazyfox';
        $result=Utility::camelToSnake($sample,'_');

        $this->assertEquals($expected,$result);
    }

    public function test_case_sensitive_string()
    {
        //to ensure it doesn't make a difference whether it is sent with capital letters or not
        $caseSensitiveSample='qUIckBrOWnFOxJuMpsOVer';//because it produces q_uick_br_own_fox_ju_mps_over
        $sample='quickBrownFoxJumpsOver';
        $caseSensitiveResult=Utility::camelToSnake($caseSensitiveSample,'_');
        $result=Utility::camelToSnake($sample,'_');

        $this->assertNotEquals($caseSensitiveResult,$result);
    }



}
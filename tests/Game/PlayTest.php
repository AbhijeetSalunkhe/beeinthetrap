<?php 

namespace Game\Tests;

use Game\Play;
use PHPUnit\Framework\TestCase;

class PlayTest extends TestCase
{

    function test_check(){
        $a = 1;
        $b = 2;
        $c = $a+$b;
        $this->assertEquals($c,3);
    }

    function test_file_exists()
    {
        $this->assertFileExists('files/testCase.json');
    }

    function test_file_is_readable()
    {
        $this->assertFileIsReadable('files/testCase.json');
    }

    function test_file_is_writable()
    {
        $this->assertFileIsWritable('files/testCase.json');
    }

    function test_testCase(){
        $play = new Play;
        $data = $play->testCase(0);
        $this->assertIsInt($data);
    }

    function test_add(){
        $play = new Play;
        $play->add('Queen',1);
        $play->add('Worker',5);
        $data = $play->add('Drone',8);
        $this->assertIsArray($data);
    }
    function test_getBeeDetails(){
        $play = new Play;
        $data = $play->getBeeDetails('Queen');
        $this->assertIsArray($data);
    }

    function test_checkAliveStatus(){
        $play = new Play;
        $data = $play->checkAliveStatus();
        $this->assertIsInt($data);
    }

    function test_totalHits(){
        $play = new Play;
        $data = $play->totalHits();
        $this->assertIsInt($data);
    }

    function test_status(){
        $play = new Play;
        $data = $play->status();
        $this->assertIsArray($data);
    }
}
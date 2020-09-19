<?php

namespace Game;

class Queen
{
    public $health = 100;
    public $damage = 8;
    public $name = 'Queen';
}

class Worker
{
    public $health = 75;
    public $damage = 10;
    public $name = 'Worker';
}

class Drone
{
    public $health = 50;
    public $damage = 12;
    public $name = 'Drone';
}

class Play
{
    
    protected $bees = [];
    public $log = [];

    public function testCase($input){
        $file = file_get_contents('files/testCase.json');
        $testCase = json_decode($file, true);

        if(!isset($testCase['key'])){
            $testCase['key'] = $input;
            $newJsonString = json_encode($testCase);
            file_put_contents('files/testCase.json', $newJsonString);
            echo "\n".'Your difficulty level: '.$input;
        } else {
            $input = $testCase['key'];
            echo "\n".'Continue with your previous level: '.$input;
        }

        echo "\n".'Values: Queen: '.$testCase['testCase'][$input]['Queen'];
        echo ' Worker: '.$testCase['testCase'][$input]['Worker'];
        echo ' Drone: '.$testCase['testCase'][$input]['Drone']. "\n"; 
        
        return $input;
    }

    public function execute($input){
        $file = file_get_contents('files/testCase.json');
        $testCase = json_decode($file, true);

        $this->add('Queen',$testCase['testCase'][$input]['Queen']);
        $this->add('Worker',$testCase['testCase'][$input]['Worker']);
        $this->add('Drone',$testCase['testCase'][$input]['Drone']);
        $status = $this->checkAliveStatus();
        if($status == 3){
            $this->bees = []; 
            $data = $this->totalHits();
            echo "\n". 'All bees are dead, Game is over with total hits '.$data['step'].' to destroy the hive. Total hit points are: '. $data['totalHits']."\n";
            $this->restart();
        } else {
            $this->getHive();
        }
    }

    public function add($bee,$count){
        $details = [
            'name' => $bee,
            'total_bees' => $count,
            'hit_points' => 0
        ];
        if(!file_exists('files/'.$bee.'.json') && $bee == 'Worker'){
            $details['life'] = 75;
            $details['damage'] = 10;
            $details['current_health'] = $count * 75;
            $fp = fopen('files/'.$bee.'.json', 'w');
            fwrite($fp, json_encode($details));
            fclose($fp);
        }
        if(!file_exists('files/'.$bee.'.json') && $bee == 'Drone'){
            $details['life'] = 50;
            $details['damage'] = 12;
            $details['current_health'] = $count * 50;
            $fp = fopen('files/'.$bee.'.json', 'w');
            fwrite($fp, json_encode($details));
            fclose($fp);
        }
        if(!file_exists('files/'.$bee.'.json') && $bee == 'Queen'){
            $details['life'] = 100;
            $details['damage'] = 8;
            $details['current_health'] = $count * 100;
            $fp = fopen('files/'.$bee.'.json', 'w');
            fwrite($fp, json_encode($details));
            fclose($fp);
        }

        $file = file_get_contents('files/'.$bee.'.json');
        $data = json_decode($file, true);

        if($bee == 'Queen' && $data['current_health'] <= 0){
            $bees = ['Worker','Drone'];
            $this->gameOver($bees);
            // $message[] = 'Queen is dead. Game over';
            // $this->log[] = $message;
        } else if($data['current_health'] > 0){
            for ($i = 0; $i < $count; $i++) {
                $this->bees[] = $bee;
            }
        } 
        return $this->bees;
    }

    public function getHive(){
        $bee = $this->random();
        if(isset($bee)){
            $getInfo = $this->getInfo($bee);
            
            if($getInfo->current_health <= 0){
                $message[] = $getInfo->name .' bee is dead, You took '. $getInfo->hit . ' hit points';
            } else {
                $data = $this->hit($getInfo);
                $message[] = 'Direct Hit. You took '. $getInfo->damage . ' hit points from a '. $getInfo->name .' bee'."\n".'Colletive health reamining for '.$getInfo->name.' bee : '. $data['current_health'];
            }
            $this->log[] = $message;
            $log = $this->getLastMessages();
            if(!empty($log)){
                foreach ($log as $message) {
                    echo "\n".$message. "\n";
                }
                $this->run();
            } 
        }
    }

    public function random(){
        if(!empty($this->bees)){
            return array_rand($this->bees, 1);
        }
    }

    public function getInfo($key){
        if(!empty($this->bees)){
            $class = $this->bees[$key];
            if($class == 'Worker'){
                $details = new Worker();
                $data = $this->getBeeDetails('Worker');
                $details->current_health = $data['current_health'];
                $details->hit = $data['hit_points'];
            } else if($class == 'Drone'){
                $details = new Drone();
                $data = $this->getBeeDetails('Drone');
                $details->current_health = $data['current_health'];
                $details->hit = $data['hit_points'];
            } else {
                $details = new Queen();
                $data = $this->getBeeDetails('Queen');
                $details->current_health = $data['current_health'];
                $details->hit = $data['hit_points'];
            }
            return $details;
        }
    }

    public function hit($bee){
        $data =  $this->getBeeDetails($bee->name);
        if (($data['current_health'] - $bee->damage) <= 0) {
            $data['current_health'] = 0;
            $newJsonString = json_encode($data);
            file_put_contents('files/'.$bee->name.'.json', $newJsonString);
        } else {
            $data['current_health'] = $data['current_health'] - $bee->damage;
            $data['hit_points'] = $data['hit_points'] + 1;
            $newJsonString = json_encode($data);
            file_put_contents('files/'.$bee->name.'.json', $newJsonString);
        }
        return $data;
    }

    public function getLastMessages(){
        return end($this->log);
    }

    public function run(){
        $this->status();
        if(!empty($this->bees)){
            $input = readline('Type hit: ');
            if($input === 'hit'){
                $this->getHive();
            } else {
                exec(sprintf('rm -rf %s', 'files/Queen.json'));
                exec(sprintf('rm -rf %s', 'files/Worker.json'));
                exec(sprintf('rm -rf %s', 'files/Drone.json'));

                $file = file_get_contents('files/testCase.json');
                $testCase = json_decode($file, true);
                if(isset($testCase['key'])){
                    unset($testCase['key']);
                    file_put_contents('files/testCase.json', json_encode($testCase));
                }
                echo "ABORTING!, Input needs to be 'hit'.\n";
                exit;
            }
        } else {
            $this->bees = []; 
            $data = $this->totalHits();
            echo "\n". 'All bees are dead, Game is over with total hits '.$data['step'].' to destroy the hive. Total hit points are: '. $data['totalHits']."\n";
            $this->restart();
        }
    }

    public function totalHits(){
        $totalHits = 0;
        $step = 0;
        $worker_data =  $this->getBeeDetails('Worker');
        $totalHits = $totalHits + ($worker_data['damage'] * $worker_data['hit_points']);
        $step = $step + $worker_data['hit_points'];

        $drone_data =  $this->getBeeDetails('Drone');
        $totalHits = $totalHits + ($drone_data['damage'] * $drone_data['hit_points']);
        $step = $step + $drone_data['hit_points'];

        $queen_data =  $this->getBeeDetails('Queen');
        $totalHits = $totalHits + ($queen_data['damage'] * $queen_data['hit_points']);
        $step = $step + $queen_data['hit_points'];

        $data = [
            'step' => $step,
            'totalHits' => $totalHits
        ];
        return $data;
    }

    public function restart(){
        $input = (string)readline('To continue, type yes : ');
        if($input == 'yes'){
            exec(sprintf('rm -rf %s', 'files/Queen.json'));
            exec(sprintf('rm -rf %s', 'files/Worker.json'));
            exec(sprintf('rm -rf %s', 'files/Drone.json'));

            $file = file_get_contents('files/testCase.json');
            $testCase = json_decode($file, true);
            if(isset($testCase['key'])){
                unset($testCase['key']);
                file_put_contents('files/testCase.json', json_encode($testCase));
            }

            $input = (int)readline('Enter a difficulty level (0:Low to 3:High) : ');
            $key = $this->testcase($input);
            $this->execute($key);
        } else {
            echo "ABORTING!\n";
            exit;
        }
    }

    public function gameOver($bees){
        foreach($bees as $bee){
            $data = $this->getBeeDetails($bee);
            $data['current_health'] = 0;
            // $data['hit_points'] = 0;
            $newJsonString = json_encode($data);
            file_put_contents('files/'.$bee.'.json', $newJsonString);
        }
    }

    public function checkAliveStatus(){
        $count = 0;
        $worker_data =  $this->getBeeDetails('Worker');
        if($worker_data['current_health'] <= 0){
            $count = $count + 1;
        }

        $drone_data =  $this->getBeeDetails('Drone');
        if($drone_data['current_health'] <= 0){
            $count = $count + 1;
        }

        $queen_data =  $this->getBeeDetails('Queen');
        if($queen_data['current_health'] <= 0){
            $count = $count + 1;
        }

        return $count;
    }

    public function status(){
        $remove = array('Worker','Drone','Queen');

        $worker_data = $this->getBeeDetails('Worker');
        if($worker_data['current_health'] <= 0){
            foreach($this->bees as $key => $value){
                if($value == 'Worker')
                {
                    
                    if(in_array($value, $remove)) unset($this->bees[$key]);
                }
            }
        }
        $drone_data = $this->getBeeDetails('Drone');
        if($drone_data['current_health'] <= 0){
            foreach($this->bees as $key => $value){
                if($value == 'Drone')
                {
                    if(in_array($value, $remove)) unset($this->bees[$key]);
                }
            }
        }

        $queen_data = $this->getBeeDetails('Queen');
        if($queen_data['current_health'] <= 0){
            foreach($this->bees as $key => $value){
                if($value == 'Queen')
                {
                    if(in_array($value, $remove)) unset($this->bees[$key]);
                }
            }
        }
        return $this->bees;
    }

    public function getBeeDetails($bee){
        $file = file_get_contents('files/'.$bee.'.json');
        $data = json_decode($file, true);
        return $data;
    }
}



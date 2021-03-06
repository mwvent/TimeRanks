<?php

namespace TimeRanks;

use pocketmine\command\ConsoleCommandSender;

class Rank{

    private $timeRanks, $name, $minutes, $PPGroup, $default, $commands = [], $message, $blocks = [], $rankName;

    /**
     * @param TimeRanks $timeRanks
     * @param $name
     * @param array $data
     * @throws \Exception
     */
    public function __construct(TimeRanks $timeRanks, $name, array $data){
        try{
            $this->timeRanks = $timeRanks;
            $this->name = $name;
            $this->default = isset($data["default"]) ? $data["default"] : false;
            $this->minutes = $this->default ? 0 : (int) $data["minutes"];
            /*
            $pureGroup = $this->timeRanks->purePerms->getGroup($data["pureperms_group"]);
            if($pureGroup !== null){
                $this->PPGroup = $pureGroup;
            }else{
                throw new \Exception("Rank has not been initialized. PurePerms group ".$data["pureperms_group"]." cannot be found");
            }
            */
            $this->rankName = $data["pureperms_group"];
            isset($data["message"]) and $this->message = $data["message"];
            isset($data["commands"]) and $this->commands = $data["commands"];
            isset($data["blocks"]) and $this->blocks = $data["blocks"];
        }catch(\Exception $e){
            if($this->isDefault()){
                $this->timeRanks->getLogger()->alert("Exception while loading default rank");
                $this->timeRanks->getLogger()->alert("Error: ".$e->getMessage());
                $this->timeRanks->getServer()->getPluginManager()->disablePlugin($this->timeRanks);
            }else{
                $this->timeRanks->getLogger()->alert("Exception while loading rank: ".$this->name);
                $this->timeRanks->getLogger()->alert("Error: ".$e->getMessage());
                if(isset($this->timeRanks->ranks[strtolower($this->name)])){
                    unset($this->timeRanks->ranks[strtolower($this->name)]);
                }
            }
        }
    }

    public function getRankName() {
        return $this->rankName;
    }
    
    public function getName(){
        return $this->name;
    }

    public function isDefault(){
        return $this->default;
    }

    public function getMinutes(){
        return $this->minutes;
    }

    public function onRankUp($playerName){
        $player = $this->timeRanks->getServer()->getPlayer($playerName);
        if($player !== null and $player->isOnline()){
            isset($this->message) ? $player->sendMessage($this->message) : $player->sendMessage("Congratulations, you are now rank ".$this->getName());
        }else{
            $player = $this->timeRanks->getServer()->getOfflinePlayer($playerName);
        }
        foreach($this->commands as $cmd){
            $this->timeRanks->getServer()->dispatchCommand(new ConsoleCommandSender(), str_ireplace("{player}", $player->getName(), $cmd));
        }
        $buddyChannelsPlugin = $this->timeRanks->getServer()->getPluginManager()->getPlugin("BuddyChannels");
        $buddyChannelsPlugin->setBaseRank($playerName, $this->getRankName());
        
        /*
        // $this->timeRanks->purePerms->getUser($player)->setGroup($this->PPGroup, null);
        $this->timeRanks->purePerms->getUserDataMgr()->setGroup($player, $this->PPGroup, null);
        $levels = $this->timeRanks->getServer()->getLevels();
        // ensure rankup affects all levels
        if( $this->timeRanks->readcfg("set-all-worlds" , false) != false) {
                foreach($levels as $level){ 
                        // $this->timeRanks->purePerms->getUser($player)->setGroup($this->PPGroup, $level->getName());
                        $this->timeRanks->purePerms->getUserDataMgr()->setGroup($player, $this->PPGroup, $level->getName());
                }
        }
         * 
         **/
    }

}

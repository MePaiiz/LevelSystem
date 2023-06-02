<?php

namespace core;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;

class Main extends PluginBase implements Listener{
    function onEnable () {
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new Run($this), 20 * 1);
		@mkdir($this->getDataFolder());
		$this->lvl = new Config($this->getDataFolder()."levelexp.yml", Config::YAML);
        $this->expn = new Config($this->getDataFolder()."exppoint.yml", Config::YAML);
        $this->expc = new Config($this->getDataFolder()."exppointneed.yml", Config::YAML);
    }
    
    function onJoin(PlayerJoinEvent $ev){
	    $p = $ev->getPlayer();
	    if(!$this->expc->get($p->getName())){
            $this->expc->set($p->getName(), 20);
            $this->expc->save();
        }
        if(!$this->expn->get($p->getName())){
            $this->expn->set($p->getName(), 0);
            $this->expn->save();
        }
        if(!$this->lvl->get($p->getName())){
            $this->lvl->set($p->getName(), 1);
            $this->lvl->save();
        }
	}
    
    public function myLevel($p){
        return $this->lvl->get($p->getName());
    }
	
	public function myExp($p){
        return $this->expn->get($p->getName());
    }
    
    public function setLevel($p, $count){
        $this->lvl->set($p->getName(), $count);
        $this->lvl->save();
    }
    
    public function setExp($p, $count){
        $this->expn->set($p->getName(), $count);
        $this->expn->save();
    }
    
    public function reduceExp($p, $count){
        $this->expn->set($p->getName(), $this->expn->get($p->getName()) - $count);
        $this->expn->save();
    }
    
    public function myNeed($p){
        return $this->expc->get($p->getName());
    }
    
    public function startLevel($p){
        $this->lvl->set($p->getName(), $this->lvl->get($p->getName()) + 1);
        $this->lvl->save();
    }
    
    public function addExpCount($p, $count){
        $this->expc->set($p->getName(), $this->expc->get($p->getName()) + $count);
        $this->expc->save();
    }
    
    public function addExp($p, $count){
        if($p instanceof Player){
            $expn = $this->myExp($p);
            $expc = $this->myNeed($p);
            $this->expn->set($p->getName(), $this->myExp($p) + $count);
            $this->expn->save();
            if($expn >= $expc){
                $this->startLevel($p);
                $this->reduceExp($p, $expc);
                $this->addExpCount($p, 10);
            }
        }
    }
    
    public function onBar(){
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $lv = $this->lvl->get($p->getName());
            $lexp = $this->expn->get($p->getName());
            $lvexp = $this->expc->get($p->getName());
            $p->sendTip("§aYourLevel§7:§f ".$lv."§8(§f".$lexp."§7/§f".$lvexp."§8)");
        }
    }
}
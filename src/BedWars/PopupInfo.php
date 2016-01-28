<?php

namespace BedWars;  

use pocketmine\math\Vector3; 
use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin; 
use pocketmine\Player; 
use pocketmine\item\Item; 
use pocketmine\level\Level;  

class PopupInfoTask extends PluginTask
{
	public $level = 0, $popupInfo = 0, $mode = 0;
	public $status = 1;
	public $owner = 0;

	public function __construct(Plugin $owner, $level, PopupInfo $popupInfo, $mode)
	{
		parent::__construct($owner);
		$this->owner = $owner;
		$this->level = $level;
		$this->popupInfo = $popupInfo;
		$this->mode = $mode;
	}

	public function onRun($currentTick)
	{
		if ($this->status) {
			$owner = $this->owner;
			$Players = ($this->level == null) ? call_user_func(function () use ($owner) {
				$Server = $owner->getServer();
				$Levels = $Server->getLevels();
				$Players = [];
				foreach ($Levels as $i => $level) {
					$Players = array_merge($Players, $level->getPlayers());
				}
				return $Players;
			}) : $this->level->getPlayers();
			if ($this->popupInfo->playersData) {
				foreach ($Players as $i => $Player) {
					if (!isset($this->popupInfo->playersData[strtolower($Player->getName())]))
						continue;
					switch ($this->mode) {
						case 0:
							$Player->sendTip(implode("\n", $this->popupInfo->playersData[strtolower($Player->getName())]));
							break;
						case 1:
							$Player->sendPopup(implode("\n", $this->popupInfo->playersData[strtolower($Player->getName())]));
							break;
					}
				}
			} else {
				foreach ($Players as $i => $Player) {
					switch ($this->mode) {
						case 0:
							$Player->sendTip(implode("\n", $this->popupInfo->rows));
							break;
						case 1:
							$Player->sendPopup(implode("\n", $this->popupInfo->rows));
							break;
					}
				}
			}
		}
	}

	public function cancel()
	{
		if ($this->getHandler() != null)
			$this->getHandler()->cancel();
	}
}

class PopupInfo
{
	public $rows = Array();
	public $playersData = 0;
	private $task = 0;

	public function __construct(Plugin $owner, $Level, $Mode)
	{
		$this->task = new PopupInfoTask($owner, $Level, $this, $Mode);
		$owner->getServer()->getScheduler()->scheduleRepeatingTask($this->task, 7);
	}

	public function resume()
	{
		$this->task->status = 1;
	}

	public function stop()
	{
		$this->task->status = 0;
	}

	public function cancel()
	{
		$this->task->cancel();
	}
}

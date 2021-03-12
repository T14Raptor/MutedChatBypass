<?php

declare(strict_types=1);

namespace T14Raptor\MutedChatBypass;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\CommandOutputPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\types\CommandOriginData;
use pocketmine\network\mcpe\protocol\types\CommandOutputMessage;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\UUID;

class Loader extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * @priority HIGHEST
	 * @ignoreCancelled true
	 */
	public function onDataPacketSend(DataPacketSendEvent $ev) : void{
		$packet = $ev->getPacket();
		if($packet instanceof TextPacket and $packet->type === TextPacket::TYPE_RAW){
			$ev->getPlayer()->sendDataPacket(self::makeCommandOutputFromText($packet->message));

			$ev->setCancelled();
		}
	}

	private static function makeCommandOutputFromText(string $message) : CommandOutputPacket{
		static $output = null;
		if($output === null){
			$msg = new CommandOutputMessage();
			$msg->isInternal = true;
			$msg->messageId = "";

			$originData = new CommandOriginData();
			$originData->type = CommandOriginData::ORIGIN_PLAYER;
			$originData->uuid = UUID::fromRandom();
			$originData->requestId = "";

			$output = new CommandOutputPacket();
			$output->originData = $originData;
			$output->outputType = 3;
			$output->successCount = 1;
			$output->messages = [$msg];
		}

		$out = clone $output;
		$out->messages[0]->messageId = $message;

		return $out;
	}
}

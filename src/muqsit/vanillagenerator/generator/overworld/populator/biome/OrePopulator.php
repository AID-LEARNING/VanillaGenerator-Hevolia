<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\object\OreType;
use muqsit\vanillagenerator\generator\overworld\populator\biome\utils\OreTypeHolder;
use muqsit\vanillagenerator\generator\Populator;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class OrePopulator implements Populator
{

	/** @var OreTypeHolder[] */
	private array $ores = [];

	/**
	 * Creates a populator for dirt, gravel, andesite, diorite, granite; and coal, iron, gold,
	 * redstone, diamond and lapis lazuli ores.
	 */
	public function __construct()
	{
		$stone = VanillaBlocks::STONE();
		$this->addOre(new OreType(VanillaBlocks::DIRT(), $stone, 0, 256, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::GRAVEL(), $stone, 0, 256, 32), 8);
		$this->addOre(new OreType(VanillaBlocks::GRANITE(), $stone, 0, 80, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::DIORITE(), $stone, 0, 80, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::ANDESITE(), $stone, 0, 80, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::COAL_ORE(), $stone, 0, 128, 16), 20);
		$this->addOre(new OreType(VanillaBlocks::IRON_ORE(), $stone, 0, 64, 8), 20);
		$this->addOre(new OreType(VanillaBlocks::GOLD_ORE(), $stone, 0, 32, 8), 2);
		$this->addOre(new OreType(VanillaBlocks::REDSTONE_ORE(), $stone, 0, 16, 7), 8);
		$this->addOre(new OreType(VanillaBlocks::DIAMOND_ORE(), $stone, 0, 16, 7), 1);
		$this->addOre(new OreType(VanillaBlocks::LAPIS_LAZULI_ORE(), $stone, 16, 16, 6), 1);
		$this->addOre(new OreType(VanillaBlocks::EMERALD_ORE(), $stone, 0, 8, 0), 1);
	}

	protected function addOre(OreType $type, int $value): void
	{
		$this->ores[] = new OreTypeHolder($type, $value);
	}

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk): void
	{
		foreach ($this->ores as $ore_type_holder) {
			for ($n = 0; $n < $ore_type_holder->value; ++$n) {

				$type = $ore_type_holder->type;
				$x = $random->nextRange($chunk_x << Chunk::COORD_BIT_SIZE, ($chunk_x << Chunk::COORD_BIT_SIZE) + Chunk::EDGE_LENGTH - 1);
				$y = $type->getRandomHeight($random);
				$z = $random->nextRange($chunk_z << Chunk::COORD_BIT_SIZE, ($chunk_z << Chunk::COORD_BIT_SIZE) + Chunk::EDGE_LENGTH - 1);
				if ($type->canPlaceObject($world, $x, $y, $z)) {
					$type->placeObject($random, $world, $x, $y, $z);
				}
			}
		}
	}
}
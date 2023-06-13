<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockTypeIds;
use pocketmine\math\VectorMath;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class OreType
{

	private Block $type;
	private int $min_y;
	private int $max_y;
	private int $amount;
	private Block $replace;

	/**
	 * Creates an ore type. If {@code min_y} and {@code max_y} are equal, then the height range is
	 * 0 to {@code min_y}*2, with greatest density around {@code min_y}. Otherwise, density is uniform
	 * over the height range.
	 *
	 * @param Block $type the block type
	 * @param int $min_y the minimum height
	 * @param int $max_y the maximum height
	 * @param int $amount the size of a vein
	 * @param int $target_type the block this can replace
	 */
	public function __construct(Block $type, Block $replace, int $min_y, int $max_y, int $amount)
	{
		$this->type = $type;
		$this->min_y = $min_y;
		$this->max_y = $max_y;
		$this->amount = ++$amount;
		$this->replace = $replace;
	}

	public function getType(): Block
	{
		return $this->type;
	}

	public function getMinY(): int
	{
		return $this->min_y;
	}

	public function getMaxY(): int
	{
		return $this->max_y;
	}

	public function getAmount(): int
	{
		return $this->amount;
	}

	/**
	 * @return Block
	 */
	public function getReplace(): Block
	{
		return $this->replace;
	}

	/**
	 * Generates a random height at which a vein of this ore can spawn.
	 *
	 * @param Random $random the PRNG to use
	 * @return int a random height for this ore
	 */
	public function getRandomHeight(Random $random): int
	{
		return $this->min_y === $this->max_y
			? $random->nextBoundedInt($this->min_y) + $random->nextBoundedInt($this->min_y)
			: $random->nextBoundedInt($this->max_y - $this->min_y) + $this->min_y;
	}


	public function canPlaceObject(ChunkManager $world, int $x, int $y, int $z): bool
	{
		return $world->getBlockAt($x, $y, $z)->hasSameTypeId($this->replace);
	}

	public function placeObject(Random $random, ChunkManager $world, int $x, int $y, int $z): void
	{
		$clusterSize = $this->amount;
		if ($clusterSize === 1 || $clusterSize === 2) {
			$world->setBlockAt($x, $y, $z, $this->type);
			return;
		}
		$angle = $random->nextFloat() * M_PI;
		$offset = VectorMath::getDirection2D($angle)->multiply($clusterSize / 8);
		$x1 = $x + 8 + $offset->x;
		$x2 = $x + 8 - $offset->x;
		$z1 = $z + 8 + $offset->y;
		$z2 = $z + 8 - $offset->y;
		$y1 = $y + $random->nextBoundedInt(3) + 2;
		$y2 = $y + $random->nextBoundedInt(3) + 2;

		for ($count = 0; $count <= $clusterSize; ++$count) {

			$seedX = $x1 + ($x2 - $x1) * $count / $clusterSize;
			$seedY = $y1 + ($y2 - $y1) * $count / $clusterSize;
			$seedZ = $z1 + ($z2 - $z1) * $count / $clusterSize;
			$size = ((sin($count * (M_PI / $clusterSize)) + 1) * $random->nextFloat() * $clusterSize / 16 + 1) / 2;

			$startX = (int)($seedX - $size);
			$startY = (int)($seedY - $size);
			$startZ = (int)($seedZ - $size);
			$endX = (int)($seedX + $size);
			$endY = (int)($seedY + $size);
			$endZ = (int)($seedZ + $size);

			for ($xx = $startX; $xx <= $endX; ++$xx) {
				$sizeX = ($xx + 0.5 - $seedX) / $size;
				$sizeX *= $sizeX;

				if ($sizeX < 1) {
					for ($yy = $startY; $yy <= $endY; ++$yy) {
						$sizeY = ($yy + 0.5 - $seedY) / $size;
						$sizeY *= $sizeY;

						if ($yy > 0 && ($sizeX + $sizeY) < 1) {
							for ($zz = $startZ; $zz <= $endZ; ++$zz) {
								$sizeZ = ($zz + 0.5 - $seedZ) / $size;
								$sizeZ *= $sizeZ;

								if (($sizeX + $sizeY + $sizeZ) < 1 && $world->getBlockAt($xx, $yy, $zz)->hasSameTypeId($this->replace)) {
									$world->setBlockAt($xx, $yy, $zz, $this->type);
								}
							}
						}
					}
				}
			}
		}
	}

}
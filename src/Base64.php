<?php

/**
 * Copyright (C) 2017 Spencer Mortensen
 *
 * This file is part of Base64.
 *
 * Base64 is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Base64 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Base64. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Spencer Mortensen <spencer@lens.guide>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2017 Spencer Mortensen
 */

namespace SpencerMortensen\Base64;

// 0...9  a...z   A...Z  _  -
// xxxxxx xxyyyy yyyyzz zzzzzz
// aaaaaa bbbbbb cccccc dddddd
class Base64
{
	public static function encode($decoded)
	{
		$bytes = unpack('C*', $decoded);

		$n = (int)ceil(count($bytes) / 3) * 3;

		ob_start();

		for ($i = 0; $i < $n; ) {
			$x = $bytes[++$i];
			$y = &$bytes[++$i];
			$z = &$bytes[++$i];

			$a = $x & 63;
			$b = (($x >> 6) & 3) | (($y << 2) & 60);
			$c = (($y >> 4) & 15) | (($z << 4) & 48);
			$d = ($z >> 2) & 63;

			echo self::getSymbol($a), self::getSymbol($b), self::getSymbol($c), self::getSymbol($d);
		}

		return rtrim(ob_get_clean(), '0');
	}

	private static function getSymbol($value)
	{
		if ($value < 10) {
			return chr(48 + $value);
		}

		if ($value < 36) {
			return chr(87 + $value);
		}

		if ($value < 62) {
			return chr(29 + $value);
		}

		if ($value === 62) {
			return '_';
		}

		return '-';
	}

	public static function decode($encoded)
	{
		$symbols = str_split($encoded, 1);

		$n = (int)ceil(count($symbols) / 4) * 4;

		ob_start();

		for ($i = 0; $i < $n; $i += 4) {
			$a = self::getValue($symbols[$i]);
			$b = self::getValue($symbols[$i + 1]);
			$c = self::getValue($symbols[$i + 2]);
			$d = self::getValue($symbols[$i + 3]);

			$x = $a | (($b << 6) & 192);
			$y = (($b >> 2) & 15) | (($c << 4) & 240);
			$z = (($c >> 4) & 3) | (($d << 2) & 252);

			echo chr($x), chr($y), chr($z);
		}

		return rtrim(ob_get_clean(), chr(0));
	}

	private static function getValue(&$symbol)
	{
		if ($symbol === null) {
			return 0;
		}

		$ord = ord($symbol);

		if ($ord === 45) {
			return 63;
		}

		if ($ord < 58) {
			return $ord - 48;
		}

		if ($ord < 91) {
			return $ord - 29;
		}

		if ($ord === 95) {
			return 62;
		}

		return $ord - 87;
	}
}

<?php

/*
 * static-overloads
 *
 * Copyright (C) 2017 SOFe
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace SOFe\StaticOverloads;

class Overload{
	/**
	 * @var Signature[]
	 */
	private $signatures = [];
	/**
	 * @var callable[]
	 */
	private $targets = [];

	/**
	 * Initializes an Overload object that stores signatures to match arguments with better performance without calling reflections frequently
	 * @param callable[] $functions
	 */
	public function __construct(array $functions){
		foreach($functions as $function){
			$rf = new \ReflectionFunction($function);
			$this->signatures[] = new Signature($rf->getNumberOfRequiredParameters(), $rf->getParameters());
			$this->targets[] = $function;
		}
	}

	public function &__invoke(array $args){
		$imperfect = null;
		foreach($this->signatures as $i => $signature){
			$match = $signature->matches($args);
			if($match === Signature::MATCH_PERFECT){
				return $this->targets[$i](...$args);
			}
			if($match === Signature::MATCH_EXTRA_ARGS){
				$imperfect = $this->targets[$i];
			}
		}
		if($imperfect !== null){
			return $imperfect(...$args);
		}
		throw new \TypeError("No matching signature with types (" . \implode(", ", \array_map(function($arg){
				return \is_object($arg) ? \gettype($arg) : \get_class($arg);
			}, $args)));
	}
}

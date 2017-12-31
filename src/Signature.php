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

class Signature{
	const MATCH_PERFECT = 2;
	const MATCH_EXTRA_ARGS = 1;
	const MATCH_ERROR = 0;

	/** @var int */
	private $required;

	/** @var Parameter[] */
	private $parameters;
	/** @var int */
	private $parameterCount;
	/** @var bool */
	private $variadic = false;
	/** @var Parameter|null */
	private $tail;

	/**
	 * @param int                    $required
	 * @param \ReflectionParameter[] $params
	 */
	public function __construct(int $required, array $params){
		$this->required = $required;
		$this->parameters = \array_map(function(\ReflectionParameter $parameter){
			return new Parameter($parameter);
		}, $params);
		$this->parameterCount = \count($this->parameters);
		if($this->parameterCount > 0){
			$this->tail = $this->parameters[$this->parameterCount - 1];
			$this->variadic = $this->tail->variadic;
		}
	}

	public function matches(array $args) : int{
		if(\count($args) < $this->required){
			return self::MATCH_ERROR;
		}
		$argsSize = \count($args);
		for($i = 0; $i < $this->parameterCount - ($this->variadic ? 1 : 0); ++$i){
			$expected = $this->parameters[$i];
			if($i >= $argsSize){
				// no more args to check!
				return $expected->optional ? self::MATCH_PERFECT : self::MATCH_ERROR;
			}
			if(!$expected->matches($args[$i])){
				return self::MATCH_ERROR;
			}
		}
		if($this->variadic){
			for($i = $this->parameterCount - 1; $i < $argsSize; ++$i){
				if(!$this->tail->matches($args[$i])){
					return self::MATCH_ERROR;
				}
			}
		}else{
			return $argsSize > $this->parameterCount ? self::MATCH_EXTRA_ARGS : self::MATCH_PERFECT;
		}
		return self::MATCH_ERROR;
	}
}

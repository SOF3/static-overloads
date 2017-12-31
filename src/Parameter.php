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

class Parameter{
	/** @var bool */
	public $typed;
	/** @var bool */
	public $scalar;
	/** @var bool */
	public $object;
	/** @var string|null */
	public $class;
	/** @var bool */
	public $nullable;
	/** @var bool */
	public $array;
	/** @var string|null */
	public $scalarType;

	/** @var bool */
	public $variadic;
	/** @var bool */
	public $optional;

	public function __construct(\ReflectionParameter $parameter){
		$this->typed = $parameter->getType() !== null;
		if(!$this->typed){
			return; // fields undefined because they are not typed will not be documented in field types
		}
		$this->scalar = $parameter->getClass() === null;
		$this->object = !$this->scalar || $parameter->getType()->getName() === "object";
		if($this->scalar){
			$this->scalarType = $parameter->getType()->getName();
		}else{
			$this->class = $parameter->getClass()->getName();
		}
		$this->array = $parameter->getType()->getName() === "array";
		$this->nullable = $parameter->allowsNull();
		$this->variadic = $parameter->isVariadic();
		$this->optional = $parameter->isOptional();
	}

	public function matches($value) : bool{
		if($this->typed){
			return true;
		}
		if($value === null){
			return $this->nullable;
		}
		if(\is_object($value)){
			if(!$this->object){
				return false;
			}
			if($this->scalar){
				return true;
			}
			// $this->class is defined
			return $value instanceof $this->class;
		}
		if(!$this->scalar){
			return false;
		}
		if($this->array && \is_array($value)){
			return true;
		}
		if($this->scalarType === \gettype($value)){
			return true;
		}
		if($this->scalarType === "float" && \is_float($value)){
			return true;
		}
		return false;
	}
}

<?php

namespace StackWeb\Renderer\Contracts;

use StackWeb\Compilers\Contracts\Token;
use StackWeb\Compilers\Stack\Structs\_ComponentPropStruct;
use StackWeb\Compilers\Stack\Structs\_ComponentSlotStruct;
use StackWeb\Compilers\Stack\Structs\_ComponentStateStruct;
use StackWeb\Compilers\Stack\Structs\_ComponentStruct;
use StackWeb\Compilers\Stack\Structs\_StackStruct;
use StackWeb\Compilers\StringReader;
use StackWeb\Renderer\Builder\SourceBuilder;

interface SourceRenderer
{

    public function __construct(StringReader $reader);

    public function renderStack(SourceBuilder $out, _StackStruct $stack) : void;

    public function renderComponent(SourceBuilder $out, _ComponentStruct $component) : void;

    public function renderComponentStates(SourceBuilder $out, _ComponentStruct $component) : void;

    public function renderComponentState(SourceBuilder $out, _ComponentStruct $component, _ComponentStateStruct $state) : void;

    public function renderComponentProps(SourceBuilder $out, _ComponentStruct $component) : void;

    public function renderComponentProp(SourceBuilder $out, _ComponentStruct $component, _ComponentPropStruct $prop) : void;

    public function renderComponentSlots(SourceBuilder $out, _ComponentStruct $component) : void;

    public function renderComponentSlot(SourceBuilder $out, _ComponentStruct $component, _ComponentSlotStruct $slot) : void;

    public function renderComponentRenderApi(SourceBuilder $out, _ComponentStruct $component) : void;

    public function renderComponentRenderCli(SourceBuilder $out, _ComponentStruct $component) : void;
    

    public function value(mixed $value) : string;

}
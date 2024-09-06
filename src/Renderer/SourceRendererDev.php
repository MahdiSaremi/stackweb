<?php

namespace StackWeb\Renderer;

use StackWeb\Compilers\ApiPhp\Structs\_ApiPhpStruct;
use StackWeb\Compilers\CliPhp\Structs\_CliPhpStruct;
use StackWeb\Compilers\Contracts\Value;
use StackWeb\Compilers\HtmlX\Structs\_DomStruct;
use StackWeb\Compilers\HtmlX\Structs\_HtmlXStruct;
use StackWeb\Compilers\HtmlX\Structs\_InvokeStruct;
use StackWeb\Compilers\HtmlX\Structs\_Node;
use StackWeb\Compilers\HtmlX\Structs\_TextStruct;
use StackWeb\Compilers\Stack\Structs\_ComponentPropStruct;
use StackWeb\Compilers\Stack\Structs\_ComponentSlotStruct;
use StackWeb\Compilers\Stack\Structs\_ComponentStateStruct;
use StackWeb\Compilers\Stack\Structs\_ComponentStruct;
use StackWeb\Compilers\Stack\Structs\_StackStruct;
use StackWeb\Compilers\StringReader;
use StackWeb\Renderer\Builder\SourceBuilder;
use StackWeb\Renderer\Builder\StringBuilder;
use StackWeb\Renderer\Contracts\SourceRenderer;
use StackWeb\Renderer\Scope\ComponentScope;

class SourceRendererDev implements SourceRenderer
{

    public function __construct(
        public readonly StringReader $reader,
    )
    {
    }

    public function renderStack(SourceBuilder $out, _StackStruct $stack) : void
    {
        $out->append("\StackWeb\StackWeb::export([\n");
        foreach ($stack->components as $component)
        {
            $out->appendObject($component->name);
            $out->append(" => fn () => ");

            $this->renderComponent($out, $component);

            $out->append(",\n");
        }
        $out->append("]);");
    }

    protected ComponentScope $componentScope;

    public function getComponentScope() : ComponentScope
    {
        return $this->componentScope;
    }

    public function renderComponent(SourceBuilder $out, _ComponentStruct $component) : void
    {
        $this->componentScope = new ComponentScope($this, $component);
        $out->append("\StackWeb\Foundation\Component::make()\n");

        $this->renderComponentProps($out, $component);
        $this->renderComponentSlots($out, $component);
        $this->renderComponentStates($out, $component);

        $this->renderComponentRenderApi($out, $component);
        $this->renderComponentRenderCli($out, $component);

        $this->renderComponentApiResults($out, $component);
        unset($this->componentScope);
    }


    public function renderComponentStates(SourceBuilder $out, _ComponentStruct $component) : void
    {
        foreach ($component->states as $state)
        {
            $this->renderComponentState($out, $component, $state);
        }
    }

    public function renderComponentState(SourceBuilder $out, _ComponentStruct $component, _ComponentStateStruct $state
    ) : void
    {
        $out->append(sprintf(
            "->state(%s, fn() => %s)\n",
            PhpRenderer::render($state->name),
            $this->value($state->default),
        ));
    }

    public function renderComponentProps(SourceBuilder $out, _ComponentStruct $component) : void
    {
        foreach ($component->props as $prop)
        {
            $this->renderComponentProp($out, $component, $prop);
        }
    }

    public function renderComponentProp(SourceBuilder $out, _ComponentStruct $component, _ComponentPropStruct $prop
    ) : void
    {
        $out->append(sprintf(
            "->prop(%s, fn() => %s)\n",
            PhpRenderer::render($prop->name),
            $this->value($prop->default),
        ));
    }

    public function renderComponentSlots(SourceBuilder $out, _ComponentStruct $component) : void
    {
        foreach ($component->slots as $slot)
        {
            $this->renderComponentSlot($out, $component, $slot);
        }
    }

    public function renderComponentSlot(SourceBuilder $out, _ComponentStruct $component, _ComponentSlotStruct $slot
    ) : void
    {
        $out->append(sprintf(
            "->slot(%s, fn() => %s)\n",
            PhpRenderer::render($slot->name),
            'null', // $this->value($state->default),
        ));
    }

    public function renderComponentApiResults(SourceBuilder $out, _ComponentStruct $component)
    {
        $out->append("->apiResults([\n");
        foreach ($this->componentScope->getApiResults() as $value => $id)
        {
            $out->appendObject($id);
            $out->append(" => fn() => (");
            $out->append($this->value($value));
            $out->append("),\n");
        }
        $out->append("])\n");
    }

    public function value(mixed $value) : string
    {
        if ($value instanceof Value)
        {
            if ($value instanceof _ApiPhpStruct)
            {
                return $value->php;
            }
            elseif ($value instanceof _CliPhpStruct)
            {
                return $value->php;
            }
        }

        return PhpRenderer::render($value);
    }

    public function valueInvoke(mixed $value, string $invoke)
    {
        if ($value instanceof Value)
        {
            return $invoke . "(" . $this->value($value) . ")";
        }

        return PhpRenderer::render($invoke($value));
    }

    public function renderComponentRenderApi(SourceBuilder $out, _ComponentStruct $component) : void
    {
        $out->append("->renderApi(fn() => ");
        $this->renderComponentHtmlXApi($out, $component, $component->render);
        $out->append(")\n");
    }

    public function renderComponentHtmlXApi(SourceBuilder $out, _ComponentStruct $component, _HtmlXStruct $htmlX)
    {
        $out->append('DomRenderer::render([');
        $this->renderHtmlXNodesApi($out, $component, $htmlX->nodes);
        $out->append('])');
    }

    public function renderHtmlXNodesApi(SourceBuilder $out, _ComponentStruct $component, array $nodes)
    {
        foreach ($nodes as $node)
        {
            $this->renderHtmlXNodeApi($out, $component, $node);
        }
    }

    public function renderHtmlXNodeApi(SourceBuilder $out, _ComponentStruct $component, _Node $node)
    {
        if ($node instanceof _DomStruct)
        {
            $out->append("['dom', ");
            $out->append($this->value($node->name) . ", ");
            $this->renderHtmlXArrayApi($out, $component, $node->props);
            $out->append(", ");
            $this->renderHtmlXNodesApi($out, $component, $node->slot);
            $out->append("], ");
        }
        elseif ($node instanceof _TextStruct)
        {
            $out->append($this->valueInvoke($node->text, 'e') . ", ");
        }
        elseif ($node instanceof _InvokeStruct)
        {
            $out->append('todo'); // todo
        }
    }

    public function renderHtmlXArrayApi(SourceBuilder $out, _ComponentStruct $component, array $values)
    {
        $out->append("[");
        foreach ($values as $key => $value)
        {
            $out->appendObject($key);
            $out->append(" => ");
            $out->append($this->value($value));
            $out->append(", ");
        }
        $out->append("]");
    }


    public function renderValueCli(StringBuilder $out, mixed $value) : void
    {
        JsRenderer::renderIn($this, $out, $value);
    }

    public function renderComponentRenderCli(SourceBuilder $out, _ComponentStruct $component) : void
    {
        $out->append("->renderCli(fn() => ");
        $out->appendString(fn ($str) => $this->renderComponentHtmlXCli($str, $component, $component->render));
        $out->append(")\n");
    }

    public function renderComponentHtmlXCli(StringBuilder $out, _ComponentStruct $component, _HtmlXStruct $htmlX)
    {
        $this->renderHtmlXNodesCli($out, $component, $htmlX->nodes);
    }

    public function renderHtmlXNodesCli(StringBuilder $out, _ComponentStruct $component, array $nodes)
    {
        $out->append("new StackWeb.Group([");
        foreach ($nodes as $node)
        {
            $this->renderHtmlXNodeCli($out, $component, $node);
        }
        $out->append("]),");
    }

    public function renderHtmlXNodeCli(StringBuilder $out, _ComponentStruct $component, _Node $node)
    {
        if ($node instanceof _DomStruct)
        {
            $out->append("new StackWeb.Dom({name: ");
            $this->renderValueCli($out, $node->name);
            $out->append(", attrs: ");
            $this->renderHtmlXArrayCli($out, $component, $node->props);
            $out->append(", slot: ");
            $this->renderHtmlXNodesCli($out, $component, $node->slot);
            $out->append("}), ");
        }
        elseif ($node instanceof _TextStruct)
        {
            $out->append("new StackWeb.Text(");
            $this->renderValueCli($out, $node->text);
            $out->append("), ");
        }
        elseif ($node instanceof _InvokeStruct)
        {
            $out->append('todo'); // todo
        }
    }

    public function renderHtmlXArrayCli(StringBuilder $out, _ComponentStruct $component, array $values)
    {
        $out->append("{");
        foreach ($values as $key => $value)
        {
            $out->appendObject($key);
            $out->append(": ");
            $this->renderValueCli($out, $value);
            $out->append(", ");
        }
        $out->append("}");
    }

    public function renderCliGetApiResult(StringBuilder $out, _ApiPhpStruct $value)
    {
        $out->append("$.getApiResult(");
        $this->renderValueCli($out, $this->getComponentScope()->apiResult($value));
        $out->append(")");
    }

}
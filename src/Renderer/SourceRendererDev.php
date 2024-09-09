<?php

namespace StackWeb\Renderer;

use Illuminate\Support\Arr;
use StackWeb\Compilers\ApiPhp\Structs\_ApiPhpStruct;
use StackWeb\Compilers\CliPhp\Structs\_CliPhpStruct;
use StackWeb\Compilers\Contracts\Value;
use StackWeb\Compilers\HtmlX\Structs\_DomPropStruct;
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
use StackWeb\ComponentNaming;
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
        $out->append("<?php\n\n\StackWeb\StackWeb::export(new \StackWeb\Foundation\Stack([\n");
        foreach ($stack->components as $component)
        {
            $out->appendObject($component->name);
            $out->append(" => fn () => ");

            $this->componentScope = new ComponentScope($this, $stack, $component);
            $this->renderComponent($out, $component);
            unset($this->componentScope);

            $out->append(",\n");
        }
        $out->append("]));");
    }

    protected ComponentScope $componentScope;

    public function getComponentScope() : ComponentScope
    {
        return $this->componentScope;
    }

    public function renderComponent(SourceBuilder $out, _ComponentStruct $component) : void
    {
        $out->append("\StackWeb\Foundation\Component::make(");
        $out->appendObject(ComponentNaming::implodeStack($this->getComponentScope()->stack->name, $component->name ?: null));
        $out->append(")\n");

        $this->renderComponentProps($out, $component);
        $this->renderComponentSlots($out, $component);
        $this->renderComponentStates($out, $component);

        $this->renderComponentRenderApi($out, $component);
        $this->renderComponentRenderCli($out, $component);

        $this->renderComponentApiResults($out, $component);

        $this->renderComponentDeps($out, $component);
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
            $out->append($value->php);
            $out->append("),\n");
        }
        $out->append("])\n");
    }

    public function renderComponentDeps(SourceBuilder $out, _ComponentStruct $component)
    {
        $out->append("->depComponents(");
        $out->appendObject($component->depComponents);
        $out->append(")\n");
    }

    public function value(mixed $value) : string
    {
        if ($value instanceof Value)
        {
            if ($value instanceof _ApiPhpStruct)
            {
                return "\$this->getApiResult(" . PhpRenderer::render($this->getComponentScope()->apiResult($value)) . ")";
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
        $out->append('\StackWeb\Renderer\DomRenderer::render([');
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
            $out->append($this->value($node->name) . ", [");
            foreach ($node->props as $prop)
            {
                $out->append($this->value($prop->name));
                $out->append(" => ");
                $out->append($this->value($prop->value));
                $out->append(", ");
            }
            $out->append("], [");
            if (isset($node->slot))
            {
                $this->renderHtmlXNodesApi($out, $component, $node->slot);
            }
            else
            {
                $out->appendObject(null);
            }
            $out->append("]], ");
        }
        elseif ($node instanceof _TextStruct)
        {
            $out->append($this->valueInvoke($node->text, 'e') . ", ");
        }
        elseif ($node instanceof _InvokeStruct)
        {
            $out->append('\StackWeb\StackWeb::invoke(');
            $out->append($this->value($node->name));
            $out->append(', [');
            foreach ($node->props as $prop)
            {
                $out->append($this->value($prop->name) . ' => ' . $this->value($prop->value) . ', ');
            }
            $out->append('], [');
            foreach ($node->slots as $slot)
            {
                $out->append($this->value($slot->name) . ' => fn () => [');
                $this->renderHtmlXNodesApi($out, $component, $slot->inner);
                $out->append('], ');
            }
            $out->append(']), ');
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
        $out->append("new StackWeb.Component({");

        $out->append("states: ($) => ({");
        foreach ($component->states as $state)
        {
            $out->append($state->name . ': ');
            $this->renderValueCli($out, $state->default);
            $out->append(', ');
        }
        $out->append("}), ");

        $out->append("slots: {");
        foreach ($component->slots as $slot)
        {
            $out->append($slot->name . ': ($) => ');
            if ($slot->default)
            {
                $this->renderHtmlXNodesCli($out, $component, $slot->default->nodes);
            }
            else
            {
                $out->append('null, ');
            }
        }
        $out->append("}, ");

        $out->append("render: ($) => ");
        $this->renderHtmlXNodesCli($out, $component, $htmlX->nodes);
        // $out->append(", ");

        $out->append("})");
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
            if (isset($node->slot))
            {
                $this->renderHtmlXNodesCli($out, $component, $node->slot);
            }
            else
            {
                $out->appendObject('null');
            }
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
            $out->append("new StackWeb.Invoke(StackWebComponents[");
            $this->renderValueCli($out, $node->name);
            $out->append("](), {");
            foreach ($node->props as $prop)
            {
                $out->append('[');
                $this->renderValueCli($out, $prop->name);
                $out->append(']: ');
                $this->renderValueCli($out, $prop->value);
                $out->append(', ');
            }
            $out->append('}, {');
            foreach ($node->slots as $slot)
            {
                $out->append('[');
                $this->renderValueCli($out, $slot->name);
                $out->append(']: () => ');
                $this->renderHtmlXNodesCli($out, $component, $slot->inner);
                // $out->append(', ');
            }
            $out->append("}), ");
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
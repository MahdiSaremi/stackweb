<?php

StackWeb::export([
    '' => fn() => FullyComponent::make()
            ->state('count', fn () => 0)
            ->render(function () {
                return "<span>{$this->get('count')}</span><button>Add</button>";
            })
            ->renderJs(function () {
                return 'c(($)=>{count:0},($)=>g([d("span",{},g([t($.g("count"))])),d("button",{onClick:()=>{$.s("count",$.g("count")+1))}},g([t("Add")]))]))';
            }),
]);

Component::export([
    'Counter' => fn () => Component::make()
        ->prop('foo', fn() => 'null')
        ->prop('bar', fn() => null)
        ->state('count', fn() => 0)
        ->renderApi(fn() => DomRenderer::render([' ', ['dom', 'div', [], 'Hello', ], ' ', ]))
        ->renderCli(fn() => 'new StackWeb.Group([new StackWeb.Text(\' \'), new StackWeb.Dom({name: \'div\', attrs: {}, slot: new StackWeb.Group([new StackWeb.Text(\'Hello\'), ]),}), new StackWeb.Text(\' \'), ]),')
    ,
]);
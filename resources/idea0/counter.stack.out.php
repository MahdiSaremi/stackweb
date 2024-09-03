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

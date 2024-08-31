<?php

return new class extends Component
{

    public function boot(Register $register)
    {
        $register->states(['userList']);
        $register->api()
    }

    public function api_updateUsers()
    {
        $state = $this->getStateManager();
        $state->userList = User::all();
    }

    public function render()
    {
        $userList = $this->get('userList');

        yield $this->make('ul', [], [
            $this->foreach($userList, fn ($user, $loop) => [
                $this->make('User', ['user' => $user])
            ]),
        ]);
        yield 'ul' => [];
        foreach ($userList as $user)
        {
            yield $this->make('User/', ['user' => $user]);
        }
        yield '/ul';

        yield 'button' => ['@click' => $api->updateUsers];
        yield new HtmlString('Refresh');
        yield '/button';
    }
    
};

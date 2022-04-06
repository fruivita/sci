<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Http\Livewire\Flash;
use Livewire\Livewire;

// Happy path
test('propriedades da mensagem success são definidas corretamente', function () {
    Livewire::test(Flash::class)
    ->call('showFlash', FeedbackType::Success->value, 'foo')
    ->assertSet('visible', '')
    ->assertSet('css', FeedbackType::Success->value)
    ->assertSet('icon', FeedbackType::Success->icon())
    ->assertSet('header', FeedbackType::Success->label())
    ->assertSet('message', 'foo');
});

test('propriedades da mensagem error são definidas corretamente', function () {
    Livewire::test(Flash::class)
    ->call('showFlash', FeedbackType::Error->value, 'foo')
    ->assertSet('visible', '')
    ->assertSet('css', FeedbackType::Error->value)
    ->assertSet('icon', FeedbackType::Error->icon())
    ->assertSet('header', FeedbackType::Error->label())
    ->assertSet('message', 'foo');
});

test('método hide reseta as propriedades', function () {
    Livewire::test(Flash::class)
    ->call('showFlash', FeedbackType::Error->value, 'foo')
    ->assertSet('visible', '')
    ->assertSet('css', FeedbackType::Error->value)
    ->assertSet('icon', FeedbackType::Error->icon())
    ->assertSet('header', FeedbackType::Error->label())
    ->assertSet('message', 'foo')
    ->call('hide')
    ->assertSet('visible', 'hidden')
    ->assertSet('css', null)
    ->assertSet('icon', null)
    ->assertSet('header', null)
    ->assertSet('message', null);
});

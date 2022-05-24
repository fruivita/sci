# Sistema de Contagem de Impressão

[![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/fruivita/sci?logo=github)](/../../releases)
[![GitHub Release Date](https://img.shields.io/github/release-date/fruivita/sci?logo=github)](/../../releases)
[![GitHub last commit (branch)](https://img.shields.io/github/last-commit/fruivita/sci/1.x?logo=github)](/../../commits/1.x)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/fruivita/sci/Unit%20and%20Feature%20tests/1.x?label=tests&logo=github)](/../../actions/workflows/tests.yml?query=branch%3Amain)
[![Test Coverage](https://api.codeclimate.com/v1/badges/ce3fc9536abe326d5766/test_coverage)](https://codeclimate.com/github/fruivita/sci/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/ce3fc9536abe326d5766/maintainability)](https://codeclimate.com/github/fruivita/sci/maintainability)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/fruivita/sci/Static%20Analysis/1.x?label=code%20style&logo=github)](/../../actions/workflows/static.yml?query=branch%3A1.x)
[![GitHub issues](https://img.shields.io/github/issues/fruivita/sci?logo=github)](/../../issues)
![GitHub repo size](https://img.shields.io/github/repo-size/fruivita/sci?logo=github)
[![GitHub all releases](https://img.shields.io/github/downloads/fruivita/sci/total?logo=github)](/../../releases)
[![GitHub](https://img.shields.io/github/license/fruivita/sci?logo=github)](../LICENSE.md)

O Sistema de Contagem de Impressão (SCI) é uma aplicação web desenvolvida utilizando-se a ***TALL Stack***:

- **[Tailwindcss](https://tailwindcss.com/docs/installation)**
- **[Alpinejs](https://alpinejs.dev/start-here)**
- **[Laravel](https://laravel.com/docs)**
- **[Livewire](https://laravel-livewire.com/docs)**

É destinado à **emissão de relatórios de impressão** e foi planejado de acordo com as necessidades da Justiça Federal do Espírito Santo. Contudo, pode ser utilizado por outros órgãos e projetos observados os termos previstos no [licenciamento](#license).

Para melhor compreender cada aspecto da aplicação, sugere-se a leitura da documentação completa disponível na **[Wiki](/../../wiki)** deste projeto.

## Table of Contents

1. [Notes](#notes)

2. [Prerequisites](#prerequisites)

3. [Installation](#installation)

4. [How it works](#how-it-works)

5. [Testing and Continuous Integration](#testing-and-continuous-integration)

6. [Changelog](#changelog)

7. [Contributing](#contributing)

8. [Code of conduct](#code-of-conduct)

9. [Security Vulnerabilities](#security-vulnerabilities)

10. [Support and Updates](#support-and-updates)

11. [Roadmap](#roadmap)

12. [Credits](#credits)

13. [Thanks](#thanks)

14. [License](#license)

---

## Notes

⭐ O SCI não é uma aplicação de bilhetagem, ou seja, de controle de permissionamento/cobrança baseado em cotas de impressão. Ele se restringe à emissão de relatórios de volume de impressão.

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Prerequisites

1. Dependências PHP

    - PHP ^8.1

    - [Extensões](https://getcomposer.org/doc/03-cli.md#check-platform-reqs)

    ```bash
    composer check-platform-reqs
    ```

2. [GitHub Package Dependencies](/../../network/dependencies)

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Installation

[Laravel Deployment](https://laravel.com/docs/deployment)

⬆️ [Voltar](#table-of-contents)

&nbsp;

## How it works

O SCI emite relatórios de impressão. Mas como os dados de impressão são incoporados à aplicação?

De maneira resumida, a aplicação espera que lhe sejam fornecidos dois tipos de arquivo:

- Estrutura corporativa do órgão;
- Log de impressão.

Esses arquivos, gerados por aplicações/scripts externos, são mapeados pelo SCI que, diariamente, os importa permitindo, a partir desses dados, a emissão dos relatórios.

Para maiores detalhes, consultar a **[Wiki](/../../wiki)** deste projeto.

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Testing and Continuous Integration

```bash
composer analyse
composer test
composer coverage
composer csfix
```

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Changelog

Por favor, veja o [CHANGELOG](CHANGELOG.md) para maiores informações sobre o que mudou em cada versão.

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Contributing

Por favor, veja [CONTRIBUTING](CONTRIBUTING.md) para maiores detalhes sobre como contribuir.

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Code of conduct

Para garantir que todos sejam bem vindos a contribuir com este projeto open-source, por favor leia e siga o [Código de Conduta](CODE_OF_CONDUCT.md).

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Security Vulnerabilities

Por favor, veja na [política de segurança](/../../security/policy) como reportar vulnerabilidades ou falhas de segurança.

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Support and Updates

A versão mais recente receberá suporte e atualizações sempre que houver necessidade. As demais, receberão atualizações por 06 meses após terem sido substituídas por uma nova versão sendo, então, descontinuadas.

| Version | PHP     | Release    | End of Life |
|---------|---------|------------|-------------|
| 1.0     | ^8.1    | 25-05-2022 | dd-mm-yyyy  |

🐛 Encontrou um bug?!?! Abra um **[issue](/../../issues/new?assignees=fcno&labels=bug%2Ctriage&template=bug_report.yml&title=%5BA+concise+title+for+the+bug%5D)**.

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Roadmap

> ✨ Alguma ideia nova?!?! Inicie **[uma discussão](https://github.com/orgs/fruivita/discussions/new?category=ideas)**.

A lista a seguir contém as necessidades de melhorias identificadas e aprovadas que serão implementadas na primeira janela de oportunidade.

Como esse projeto destina-se a um cliente específico, só serão implementadas funcionalides aprovadas internamente pela equipe da Justiça Federal do Espírito Santo, de acordo com a conveniênica e a oportunidade.

- [ ] n/a

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Credits

- [Fábio Cassiano](https://github.com/fcno)

- [All Contributors](/../../contributors)

⬆️ [Voltar](#table-of-contents)

&nbsp;

## Thanks

👋 Agradeço às pessoas e organizações abaixo por terem doado seu tempo na construção de projetos open-source que foram usados nesta aplicação.

- ❤️ [The Laravel Framework](https://github.com/laravel) pelos packages:

  - [laravel/framework](https://github.com/laravel/framework)

  - [laravel/fortify](https://github.com/laravel/fortify)

  - [laravel/tinker](https://github.com/laravel/tinker)

  - [laravel/sail](https://github.com/laravel/sail)

- ❤️ [Livewire](https://github.com/livewire) pelo package [livewire/livewire](https://github.com/livewire/livewire)

- ❤️ [DirectoryTree](https://github.com/DirectoryTree) pelo package [directorytree/ldaprecord-laravel](https://github.com/directorytree/ldaprecord-laravel)

- ❤️ [Guzzle](https://github.com/guzzle) pelo package [guzzle/guzzle](https://github.com/guzzle/guzzle)

- ❤️ [Blade UI Kit](https://github.com/blade-ui-kit) pelo package [blade-ui-kit/blade-icons](https://github.com/blade-ui-kit/blade-icons)

- ❤️ [Barry vd. Heuvel](https://github.com/barryvdh) pelos packages:

  - [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)

  - [barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar)

- ❤️ [Jonas Staudenmeir](https://github.com/staudenmeir) pelo package [staudenmeir/eloquent-eager-limit](https://github.com/staudenmeir/eloquent-eager-limit)

- ❤️ [FakerPHP](https://github.com/FakerPHP) pelo package [fakerphp/faker](https://github.com/fakerphp/faker)

- ❤️ [FriendsOfPHP](https://github.com/FriendsOfPHP) pelo package [FriendsOfPHP/PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

- ❤️ [Mockery](https://github.com/mockery) pelo package [mockery/mockery](https://github.com/mockery/mockery)

- ❤️ [Nuno Maduro](https://github.com/nunomaduro) pelos packages:

  - [nunomaduro/collision](https://github.com/nunomaduro/collision)

  - [nunomaduro/larastan](https://github.com/nunomaduro/larastan)

- ❤️ [PEST](https://github.com/pestphp) pelos packages:

  - [pestphp/pest](https://github.com/pestphp/pest)

  - [pestphp/pest-plugin-faker](https://github.com/pestphp/pest-plugin-faker)
  
  - [pestphp/pest-plugin-laravel](https://github.com/pestphp/pest-plugin-laravel)

- ❤️ [PHPStan](https://github.com/phpstan) pelos packages:

  - [phpstan/phpstan](https://github.com/phpstan/phpstan)

  - [phpstan/phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules)

- ❤️ [Sebastian Bergmann](https://github.com/sebastianbergmann) pelo package [sebastianbergmann/phpunit](https://github.com/sebastianbergmann/phpunit)

- ❤️ [Spatie](https://github.com/spatie) pelos packages:

  - [spatie/laravel-ignition](https://github.com/spatie/laravel-ignition)

  - [spatie/pest-plugin-test-time](https://github.com/spatie/pest-plugin-test-time)

- ❤️ [ergebnis](https://github.com/ergebnis) pelo package [ergebnis/composer-normalize](https://github.com/ergebnis/composer-normalize)

- ❤️ [Shivam Mathur](https://github.com/shivammathur) pela Github Action [shivammathur/setup-php](https://github.com/shivammathur/setup-php)

- ❤️ [GP](https://github.com/paambaati) pela Github Action [paambaati/codeclimate-action](https://github.com/paambaati/codeclimate-action)

- ❤️ [Stefan Zweifel](https://github.com/stefanzweifel) pelas Github Actions:

  - [stefanzweifel/git-auto-commit-action](https://github.com/stefanzweifel/git-auto-commit-action)

  - [stefanzweifel/changelog-updater-action](https://github.com/stefanzweifel/changelog-updater-action)

- ❤️ [Bootstrap](https://github.com/twbs) pelo package [twbs/icons](https://github.com/twbs/icons)

- ❤️ [Alpine.js](https://github.com/alpinejs) pelo package [alpinejs/alpine](https://github.com/alpinejs/alpine)

- ❤️ [PostCSS](https://github.com/postcss) pelos packages:
  - [postcss/autoprefixer](https://github.com/postcss/autoprefixer)
  
  - [postcss/postcss](https://github.com/postcss/postcss)

- ❤️ [flatpickr](https://github.com/flatpickr) pelo package [flatpickr/flatpickr](https://github.com/flatpickr/flatpickr)

- ❤️ [Laravel Mix](https://github.com/laravel-mix) pelo package [laravel-mix/laravel-mix](https://github.com/laravel-mix/laravel-mix)

- ❤️ [Tailwind Labs](https://github.com/tailwindlabs) pelo package [tailwindlabs/tailwindcss](https://github.com/tailwindlabs/tailwindcss)

💸 Algumas dessas pessoas ou organizações possuem alguns produtos/serviços que podem ser comprados. Se você puder ajudá-los comprando algum deles ou se tornando um patrocinador, mesmo que por curto período, ajudará toda a comunidade **open-source** a continuar desenvolvendo soluções para todos.

⬆️ [Voltar](#table-of-contents)

&nbsp;

## License

The MIT License (MIT). Por favor, veja o **[License File](../LICENSE.md)** para maiores informações.

⬆️ [Voltar](#table-of-contents)

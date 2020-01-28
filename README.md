# apie
[![CircleCI](https://circleci.com/gh/pjordaan/apie.svg?style=svg)](https://circleci.com/gh/pjordaan/apie)
[![codecov](https://codecov.io/gh/pjordaan/apie/branch/master/graph/badge.svg)](https://codecov.io/gh/pjordaan/apie/)
[![Travis](https://api.travis-ci.org/pjordaan/apie.svg?branch=master)](https://travis-ci.org/pjordaan/apie)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pjordaan/apie/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pjordaan/apie/?branch=master)

library to convert simple POPO's (Plain Old PHP Objects), DTO (Data Transfer Objects) and Entities to a REST API with OpenAPI spec. It's still a work in progress,
but there are tons of unit tests and a bridge to integrate the library in [Laravel](https://github.com/pjordaan/laravel-apie).

Since Apie version 3 it is also possible to add plugins to be modular.

Documentation:
1. [Installation](/docs/01-installation.md)
2. [How the mapping works](/docs/02-explaining-restful-objects.md)
3. [PSR Controllers/routing](/docs/03-controllers.md)
4. [Search filters](/docs/04-search-filters.md)
5. [Apie plugins](/docs/05-plugins.md)
6. [Versioning](/docs/06-versioning.md)

## Apie vs. Api Platform
This library is heavily inspired by the Symfony Api Platform, but there are some changes:
- This library is framework agnostic and requires a wrapper library to make it work in a framework. Api Platform core is framework agnostic, but it is hard to setup outside the symfony framework.
- In the Api Platform a resource provider or persister determines if it can persist or retrieve a specific resource with a supports() method. For Apie the resource class is explicitly linked to a service making it easier to select which HTTP methods are available.
- API Platform has no default serialization group if no serialization group is selected.
- So far APIE has less functionality for standards (JSON+LD, HAL) and no GraphQL support. Eventually we might add it.
- APIE is better capable of having api resources without an id.

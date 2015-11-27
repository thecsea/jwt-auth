# jwt-auth

> JSON Web Token Authentication for Laravel

Build status: [![Build Status](https://travis-ci.org/thecsea/jwt-auth.svg?branch=master)](https://travis-ci.org/thecsea/jwt-auth) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecsea/jwt-auth/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thecsea/jwt-auth/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/thecsea/jwt-auth/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/thecsea/jwt-auth/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/thecsea/jwt-auth/badges/build.png?b=master)](https://scrutinizer-ci.com/g/thecsea/jwt-auth/build-status/master) [![Latest Stable Version](https://poser.pugx.org/thecsea/jwt-auth/v/stable)](https://packagist.org/packages/thecsea/jwt-auth) [![Total Downloads](https://poser.pugx.org/thecsea/jwt-auth/downloads)](https://packagist.org/packages/thecsea/jwt-auth) [![Latest Unstable Version](https://poser.pugx.org/thecsea/jwt-auth/v/unstable)](https://packagist.org/packages/thecsea/jwt-auth) [![License](https://poser.pugx.org/thecsea/jwt-auth/license)](https://packagist.org/packages/thecsea/jwt-auth)

See the [official WIKI](https://github.com/tymondesigns/jwt-auth/wiki) for documentation

This is a fork of [tymondesings/jwt-auth](https://github.com/tymondesigns/jwt-auth/) that implements:

* Standard laravel traits adapted to work with json call (no redirect) like [thecsea/laravel-noredirect-traits](https://github.com/thecsea/laravel-noredirect-traits) for normal login
* Some improvements to traits to make them more customizable (it's possible use more than one credential)
* Middleware improved, new middleware that joins the original two ones
* ActingAs trait implemented for test
* Custom claims fixed, now they are checked (even in middleware)

# Examples and test
New method are not tested since they are used and tested directly in another project [dsd-meetme/backend](https://github.com/dsd-meetme/backend)

# License

The MIT License (MIT)

Copyright (c) 2014 Sean Tymon

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

# By [thecsea.it](http://www.thecsea.it)

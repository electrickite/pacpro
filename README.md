PacPro: MODX Package Provider
=============================

A simple transport package provider for MODX Revolution. The intent is to
provide a minimal web front-end in front of a directory of transport packages
and files containing package metadata.

This project is an incomplete implementaton of a package provider and is missing
a number of features supported by the official modx.com provider (notably
download counts and pagination). However, it should enable a MODX site to search
for and download transport packages.

## Requirements

  * [PHP](http://php.net) >= 5.6
  * [Composer](https://getcomposer.org)

PacPro has been tested on Unix-like operating systems.

## Installation

Clone the repository and install dependencies using Composer:

    $ git clone https://github.com/electrickite/pacpro.git
    $ cd pacpro
    $ composer install

Application-wide configuration can be passed in using environment variables or
a `.env` file. Copy `.env.example` and modify as needed.

    $ cp .env.example .env

## Use

To run locally with the built-in PHP web server:

    $ php -S localhost:8000 -t public index.php

And then access the application at ex: [http://localhost:8000/verify](http://localhost:8000/verify)

### Endpoints

The application supports a number of endpoints and parameters used by the MODX
package installer. 

  * `/verify` - used by MODX to test if a URL is a package provider
  * `/home` - Shows most popular and most recent packages across all repositories
  * `/repository` - Index of all repositories
  * `/repository/main` - Show repository `main`
  * `/package` - Index of all packages
  * `/package?tag=main` - List all packages in the `main` repository
  * `/package?query=foo` - Search package names for `foo`
  * `/package?signature=sample-1.2.3-pl` - Find version `1.2.3-pl` of the`sample` package
  * `/package/update?signature=sample-1.2.3-pl` - Check if an udated version of the `sample` package exists
  * `/download/sample-1.2.3-pl` - Download the transport file for version 1.2.3-pl of `sample`
  * `/download/sample-1.2.3-pl?getUrl=1` - Return the download URL for version 1.2.3-pl of `sample`

### Production

When running in production, development and testing dependencies can be excluded
with:

    $ composer install --no-dev

Set the web server document root to the `public` directory. Instead of a `.env`
file, configuration can be passed to the application using envrironment
variables set in the web server configuration. (But `.env` works, too!)

## Package directory

The transport packages and associated metadata served by the provider are stored
in a simple directory structure on the filesystem. By default, the root of the
package hierarchy is assumed to be `<project root>/packages`, but this path can
be configured using the `PACKAGES_PATH` environment variable.

Directory and file names should not include "problematic" characters such as
spaces or slashes. It is safest to stick with: `[A-Za-z0-9_.-]`

The structure of the package directory is as follows:

    packages/
      +-> info.yml
      +-> repository1/
            +-> info.yml
            +-> package1/
                  +-> info.yml
                  +-> package1-1.0.0-pl.transport.zip
                  +-> package1-1.1.0-pl.transport.zip
            +-> package2/
                  +-> info.yml
                  +-> package1-2.1.4-pl.transport.zip
                  +-> package1-2.5.0-pl.transport.zip
      +-> repository2/
            +-> info.yml
            +-> ...

An example package hierarchy can be found at `tests/fixtures/packages`.

### Root

The root directory contains a [YAML](http://yaml.org) metadata file named
`info.yml` describing popular and recent packages, and at least one subdirectory
representing a package repository.

Example root `info.yml`:

    # An array of popular packages in <repo>/<package> format
    popular:
      - main/sample
      - other/foo
    # An array of the most recent packages in <repo>/<package> format
    newest:
      - main/sample

### Repository

Each repository directory contains a YAML metadata file named `info.yml` that
describes the repository and a subdirectory for each package contained in the
repository. Note that the `id` field in the metadata file must match the
directory name.

Example repository `info.yml`:

    id:           main                      # Repo ID (matches directory name)
    name:         Main                      # Repository display name
    description:  The main repository       # Repository description
    created_on:   2017-12-05T14:37:16+0000  # Repository creation date
    rank:         0                         # Rank order of repository
    templated:    false                     # Repository contains templates

### Package

Each package directory contains a YAML metadata file named `info.yml` and
transport package files for each version of the package. Note that the `id`
field in the metadata file must match the package directory name. In addition,
each package ID/directory must by unique across packages from _all_
repositories.

Example repository `info.yml`:

    id:           sample          # Package ID (matches directory name)
    name:         Sample package  # Package display name
    description:  Just a sample   # Package description
    instructions: Install me      # Installation instructions (optional)
    changelog:    Fixed bugs      # Changelog (optional)
    current:      1.2.3-pl        # Current version (from transport file name)
    author:       John Doe        # Author name (optional)
    license:      custom          # License name (optional)
    modx_max:     2.6             # Maximum supported MODX version (optional)
    modx_min:     2.2             # Minimum supported MODX version (optional)
    supports_db:  mysql,sqlsrv    # Supported databases (optional)

### Transport files

Package directories must have one or more transport files, one for each version
of the package. Transport file names must use the following format:
`<package_id>-<version>-<release>.transport.zip`

PacPro does not create transport files. See the [MODX documentation](https://docs.modx.com/revolution/2.x/case-studies-and-tutorials/developing-an-extra-in-modx-revolution)
for more information on creating transport packages.

## Authentication

The package provider can be restricted to authorized users by adding a
`users.yml` file to the root of the package hierarchy. The format of this file
is:

    hashed_keys: false     # API key hashing control
    users:
      myuser:  mypassword  # username: api_key
      user2:   password2

If `hashed_keys` is set to `true`, API keys can be stored in a hashed format.
Use the inclided password hashing utility to generate API key hashes:

    $ bin/hash_key mypassword
    $2y$10$Ogui0BV5HRnWj1pD6G83ZOh0V.yXk8E1SNtiL4Wn6.Hv96ZO.YGuq

Authenticated requests use the `username` and `api_key` query string parameters
to pass credentials, ie: `http://localhost:8000/verify?username=myuser&api_key=mypassword`

## Tests

Run unit and functional tests with PHPUnit:

    $ vendor/bin/phpunit

## Author

  * Created by: Corey Hinshaw <coreyhinshaw@gmail.com>

# License and copyright

Copyright 2017 Corey Hinshaw

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.


# terminal42/contao-oembed

A Contao 4 bundle to integration several Twitter widgets into a website.

https://dev.twitter.com/web/overview


## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require terminal42/contao-oembed "^1.0@dev"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Terminal42\OEmbedBundle\Terminal42TwitterBundle(),
        );

        // ...
    }

    // ...
}
```


## Features

### 1. Single Tweet

A new content element to embedd a single tweet.


## License

This bundle is released under the [LGPL-3.0 license](LICENSE)

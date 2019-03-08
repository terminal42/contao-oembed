
# terminal42/contao-oembed

A Contao 4 bundle that provides several oEmbed widgets (Twitter, Instagram) for a website.


## Features

This bundle provides content elements that can be added to your website.

1. Single Tweet  
A new content element to embed a single tweet.

2. Twitter user timeline   
A new content element to embed a user's timeline.

3. Instagram post  
Embed an Instagram post into your website.

## How does it work?

You provide a link to a tweet or post, the extension will fetch and output the embed code by the oEmbed provider.


## Installation

Choose the installation method that matches your workflow!

### Installation via Contao Manager

Search for `terminal42/contao-oembed` in the Contao Manager and add it to your installation. Finally, update the 
packages.

### Manual installation

Add a composer dependency for this bundle. Therefore, change in the project root and run the following:

```bash
composer require terminal42/contao-oembed
```

Depending on your environment, the command can differ, i.e. starting with `php composer.phar …` if you do not have 
composer installed globally.

Then, update the database via the Contao install tool.


## License

This bundle is released under the [LGPL-3.0 license](LICENSE)

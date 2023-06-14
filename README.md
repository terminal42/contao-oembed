
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

4. Facbeook page / post / video
Embed a Facebook post, page or video into your website.

5. TikTok video
   Embed a TikTok video into your website.


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


## Configuration

The Facebook API requires an access key to fetch oEmbed for Facebook or Instagram content.

You need to register your Contao installation as an app on facebook and create a
app token according to the documentation:
https://developers.facebook.com/docs/facebook-login/access-tokens#apptokens

The app token (or client token) can be set in the bundle configuration:
```
// config/config.yml
terminal42_oembed:
    facebook_token: 'xxxx|xxxx'
```

**WARNING:** If you do not configure a Facebook token, the extension will use a _generic_
app authentication that is controlled by terminal42 and has request limits set
by Facebook upon which we have no influence. The API token can be invalidated at any time
without notice by terminal42 or Facebook.


## License

This bundle is released under the [MIT license](LICENSE)

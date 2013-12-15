# AMDJS

This module enables the use of AMD JavaScript modules with Magento.

## Why

If you're wondering why AMD, may I [refer you elsewhere](http://requirejs.org/docs/whyamd.html)?

It's certainly possible to let require.js take care of module loading, but in a production environment, it fires off a lot of extra http requests, which nobody really wants.

The r.js optimiser does a great job of this, and this module is designed to replace it. The problem with the optimiser is that it doesn't allow very easy compilation with Magento projects. One is reduced to parsing the layouts by hand and hoping it's correct. This does it all automatically.

## How it works

The modules should be placed in the directory `js/maxbucknell/amdjs/modules`, and all modules should be named relative to that location.

Then, in the layout xml, simply include the modules you need:

```xml
<reference name="amdjs_modules">
    <action method="addModule"><module>my/module/name</action>
</reference>
```

Then, when the page loads, the modules will be collected, and compiled into one file along with the dependencies. This file will be loaded in the `before_body_end` block.

This system uses require.js as its loader of choice, but no asynchronous http requests will be made. This module compiles all modules and puts them into one file that loads at the end of the body. Like a best practice.

## Config Options

Configuration is done in the backend. It can be found in the developer section of the configuration. The options presented are thus:

+ ### Module source files location

  This allows you to customise where your modules are created. Especially useful if you get sick of seeing my name everywhere. It should be a path relative to the base directory of the Magento installation.

+ ### Module aliases

  It is possible to configure aliases similar to those in require.js. Really great if you're using a front end package manager like Bower, or if your jQuery is named with a version.

  This field takes a JSON object agreeing to the same specification as require.js. My aim was to make transitioning to this as easy as possible.

+ ### Enable minification

  Any questions? This is not a trick option.

+ ### Custom minifier

  By default, this module ships with a PHP JavaScript minifer called JShrink. But it turns out that the best minifiers are not written in PHP. I didn't want to enforce another runtime on a user, but if you've got node installed, I highly recommend upgrading the minifier.

  Specifically, the hotness at the moment is UglifyJS2. If you've got it installed, here's how you might do it:

  > uglifyjs2 {{file}} -mc

  The {{file}} variable is an ugly fix, but it allows you to use programmes that don't just take the input at the end of the command. Ideally, this would be passed to stdin, but PHP's `shell_exec` function doesn't think a lot of large inputs.

  I'll be working on fixing this in the next version, though.

+ ### Enable caching

  See "Enable minification"

+ ###  Disable caching and minification when in developer mode.

  This setting allows you to have minification turned on in a staging environment, but not in a local or shared development setup, while sharing a database. How about that?

## Contributions

I welcome them, of any kind. I write kinda sucky PHP code, and I'm quite new to Magento, which means there are a lot of things I might not be doing the correct way, or whatever. It's a complicated system, or something. So please help me out here :-)

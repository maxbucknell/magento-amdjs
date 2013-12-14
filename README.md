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

Currently there is no painless, upgradeproof way of configuring this thing. I suppose you could configure it in some other xml file, but that's not exactly clean. I'll be adding an admin interface at some point.

Configuration can be done via the module's config.xml, though. The options you have are to disable minification and caching when not in developer mode, and to add path aliases, similar to those found in require.js.

The minifation and caching can be disabled by giving the xpaths `config/default/MaxBucknell_AMDJS/settings/disableMinification` and `disableCaching` values of `true`.

Path aliases are configured under the xpath `config/default/MaxBucknell_AMDJS/settings/aliases`. The module will look at every child node, and search each one of those for a `from` node and a `to` node, using those to map between module names and the filesystem.

## Contributions

I welcome them, of any kind. I write kinda sucky PHP code, and I'm quite new to Magento, which means there are a lot of things I might not be doing the correct way, or whatever. It's a complicated system, or something. So please help me out here :-)

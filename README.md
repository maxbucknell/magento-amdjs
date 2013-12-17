# AMDJS

This module enables the use of AMD JavaScript modules with Magento.

## Why

If you're wondering why AMD, may I [refer you elsewhere][1]?

It's certainly possible to let require.js take care of module loading, but in a production environment, it fires off a lot of extra http requests, which nobody really wants.

The r.js optimiser does a great job of this, and this module is designed to replace it. The problem with the optimiser is that it doesn't allow very easy compilation with Magento projects. One is reduced to parsing the layouts by hand and hoping it's correct. This does it all automatically.

## How

The block that runs this show will, in the end, output two JavaScript files. One will be require.js, and the other will be the a big mush of all the modules that are loaded into any one page.

Controlling which modules are loaded into the page is done via the layout xml. The block is defined with a name of `amdjs_modules`. Simply reference this block, and modules can be added, or removed from the page, with the `addModule` and `removeModule` methods, respectively, both of which take a single argument, the module name.

For example. Suppose you have a directory of AMD compliant modules in a directory that you have declared to be the soure files location in the back end. Perhaps it looks like this:

```
amd
├── base.js
├── slider.js
├── tooltips.js
└── vendor
    └── lib.js
```

Say, for the sake of example that base depends on slider and tooltip, which each depend on some vendor/lib module. In this case, a portion of one's local.xml might look like this:

```xml
<default>
    <!-- ... -->
    <reference name="amdjs_modules">
        <action method="addModule"><module>base</module></action>
    </reference>
    <!-- ... -->
</default>
```

The base module would then be parsed, its dependencies found, their dependencies found, and so on until all dependencies were listed. Note that this process eliminates duplicates, so although slider and tooltip both depend on the (probably large) vendor/lib, it is only included once.

Then, these files are all concatenated, and saved into a temporary location. If caching is enabled, a note of this location is made, and the modules are not recompiled on each request. If minification is enabled, that step is also performed now.

But it turns out that we don't actually want base to run on the checkout page. We'd better add something else to our layout file.

```xml
<checkout_index_index>
    <!-- ... -->
    <reference name="amdjs_modules">
        <action method="removeModule"><module>base</module></action>
    </reference>
    <!-- ... -->
</checkout_index_index>
```

Now, base will not be loaded, nor will its dependencies, unless, of course, some other module needs them. And there we have it. That's the public API, folks. It's a pretty simple interface. It's not actually any harder than Magento's version. But it is better.

## What's Wrong With the Normal Functionality?

By default, Magento has various methods in the `head` block to add JavaScript. Straight away we can identify one problem. Scripts, in general, should not load in the `<head>` of a document. This blocks the page load, and if a few hundred kilobytes of JavaScript have to load before the user sees anything, we know we can do better.

Second, this just loads JavaScript files one after the other. By enforcing the AMD syntax, a certain amount of safety is implied. There's no reason that a second version of jQuery added by a third party module can't overwrite the one you were using. If you've added plugins to that version, you can get some pretty nasty bugs. That's the trouble with global variables, and one of many reasons why they should be avoided.

Helpfully, require.js cries whenever you try to redefine a module that has already been defined. So this can't happen.

## Config Options

Configuration is done in the backend. It can be found in the developer section of the configuration. The following options are presented:

+ ### Module source files location

  This allows you to customise where your modules are created. Especially useful if you get sick of seeing my name everywhere. It should be a path relative to the base directory of the Magento installation.

+ ### Module aliases

  It is possible to configure aliases similar to [those in require.js][2]. Really great if you're using a front end package manager like [Bower][3], or if your [jQuery is named with a version][4].

  This field takes a JSON object agreeing to the same specification as require.js. My aim was to make transitioning to this as easy as possible.

  The differences is that currently, it is assumed that this is a path relative to the base source files directory. Loading from a remote host or an absolute path relative to the web root is currently not permitted.

+ ### Enable minification

  Any questions? This is not a trick option.

+ ### Custom minifier

  By default, this module ships with a PHP JavaScript minifer called JShrink. But it turns out that the best minifiers are not written in PHP. I didn't want to enforce another runtime on a user, but if you've got node installed, I highly recommend upgrading the minifier.

  Specifically, the hotness at the moment is [UglifyJS2][5]. If you've got it installed, here's how you might do it:

  > uglifyjs2 {{file}} -mc

  The {{file}} variable is an ugly fix, but it allows you to use programmes that don't just take the input at the end of the command. Ideally, this would be passed to stdin, but PHP's `shell_exec` function doesn't think a lot of large inputs.

  I'll be working on fixing this in the next version, though.

+ ### Enable caching

  See "[Enable minification][6]"

+ ###  Disable caching and minification when in developer mode.

  This setting allows you to have minification turned on in a staging environment, but not in a local or shared development setup, while sharing a database. How about that?

## Contributions

I welcome them, of any kind. I write kinda sucky PHP code, and I'm quite new to Magento, which means there are a lot of things I might not be doing the correct way, or whatever. It's a complicated system, or something. So please help me out here :-)

[1]: http://requirejs.org/docs/whyamd.html
[2]: http://requirejs.org/docs/api.html#config-paths
[3]: http://bower.io
[4]: http://requirejs.org/docs/jquery.html#modulename
[5]: https://github.com/mishoo/UglifyJS2
[6]: #enable-minification
[7]: http://requirejs.org
[8]: https://github.com/jrburke

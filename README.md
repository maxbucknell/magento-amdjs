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

This system uses require.js as its loader of choice, but no asynchronous calls will be made.

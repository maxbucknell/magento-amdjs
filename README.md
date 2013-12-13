# AMDJS

This module enables the use of AMD JavaScript modules with Magento.

## Why

## How it works

The modules should be placed in the directory `js/maxbucknell/amdjs/modules`, and all modules should be named relative to that location.

Then, in the layout xml, simply include the modules you need:

```xml
<reference name="amdjs_modules>
    <action method="addModule"><module>my/module/name</action>
</reference>
```

Then, when the page loads, the modules will be collected, and compiled into one file along with the dependencies. This file will be loaded in the `before_body_end` block.

This system uses require.js as its loader of choice, but no asynchronous calls will be made.


# Craft-utils

This is a collection a Craft utilities to ease custom development on Craft CMS.

## `AbstractModule`

This base class for custom modules aims at making less painful to create a Craft
module.
Most of the time, all you have to do to register/customize the following 
components is to override the corresponding getter:
  - translation cateory (resonnable default value provided)
  - CP template roots (resonnable default value provided)
  - site template roots (resonnable default value provided)
  - twig extensions
  - CP nav items
  - CP routes
  - site routes
  - User permissions
  - Craft variables additions
  - element types
  - view hooks

⚠️ There may be some performance implications as the getters are executed on 
each request. Do as little as possible in the getters, and return early if 
possible.
If you need to further optimize your code, it's easy to get rid of this module
and register the components the traditional way.

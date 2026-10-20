Manage storage Filesystems
==========

When you need to update / create a storage filesystem

## 1. KnpGaufrette

We are using KnpGaufrette to handle our different storage filesystem.

You can find the configuration here : config/packages/knp_gaufrette.yaml

For now, we are only using google storage buckets and local filesystem adapters. But you can implement anyone you want.

## 2. Services

Once your adapter is defined, you can inject it in a service to use it throughout the application.

You can find already configured services here : src/Services/Filesystem

Those service configuration can be found here : src/config/services/filesystem.yaml

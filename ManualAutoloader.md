[<< Engine](ManualEngine.md) | **Autoloader** | [Session Factory >>](ManualSessionFactory.md)
# Autoloader #

Querying objects from a Session means that it is quite possible to instantiate an instance of a class for a class that has not yet been loaded. To account for this, Repose provides the concept of an Autoloader to attempt to load classes on demand.

Autoloaders are a very necessary evil and need to be discussed here, but day to day a user of Repose does not need to know too much about how an Autoloader works, just that it exists. One of the primary reasons for the existence of Session Factories is hiding the existence and creation of Repose Engines.

Autoloader will be seeing a lot of changes as this part probably needs to be far more flexible and configurable. How classes are stored and how the names of the class relate to the filename in which they are contained is... quite varied in the world of PHP.

Until the default Autoloaders pattern rules are beefed up some, the Callback Autoloader will probably be used quite a bit as it provides the most flexibility.

## Classpath Autoloader ##

By default, a Classpath Autoloader will be selected if one is not specified. The Classpath Autloader will look for a file in the include paths that matches the class name with a .php extension. If it is found, the file is required.

For example, if a class named `Project` needs to be loaded, the Classpath Autoloader will search for `Project.php` in all of the directories in the PHP include paths and attempt to load it if found.

## Path Autoloader ##

A Path Autloader may be used if the classes are known to exist at specific paths.

```
$autoloader = new repose_PathAutoloader('/path/to/entities');
```

In the above case, if a class named `Project` needs to be loaded, the Path Autoloader will search for `/path/to/entities/Path.php`.

## Callback Autoloader ##

A Callback Autoloader is provided to allow for inline class loading.

```
$autoloader = new repose_CallbackAutoloader(array($this, 'loadClass'));
```

The callback will be passed on argument, the name of the class to be loaded.

```
public function loadClass($clazz) {
    // Custom code to handle loading class named $clazz
}
```


---

[<< Engine](ManualEngine.md) | **Autoloader** | [Session Factory >>](ManualSessionFactory.md)
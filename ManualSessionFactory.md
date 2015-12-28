[<< Autoloader](ManualAutoloader.md) | **Session Factory** | [Configuration Session Factory >>](ManualConfigurationSessionFactory.md)
# Session Factory #

The purpose of a Session Factory is to manage the creation of Repose [Sessions](ManualSession.md). A Session is defined by a class [Mapping](ManualMapping.md), a persistence [Engine](ManualEngine.md) and an [Autoloader](ManualAutoloader.md). Session Factories are not strictly necessary, but they can definitely make life easier on a developer as it can simplify things quite a bit.

## Current Session ##

A Session Factory should provide a way to access the current session. This is a convenience concept so that any part of code that has access to the Session Factory can access the same Session as any other part of the code that can access the Session Factory.

```
$session = $sessionFactory->currentSession();
```

## Open a Session ##

Open an arbitrary Session. There is no way to get access to this Session again once it has fallen out of scope.

```
$newSession = $sessionFactory->openSession();
```


---

[<< Autoloader](ManualAutoloader.md) | **Session Factory** | [Configuration Session Factory >>](ManualConfigurationSessionFactory.md)
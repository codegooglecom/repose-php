[<< Mapping](ManualMapping.md) | **Engine** | [Autoloader >>](ManualAutoloader.md)
# Engine #

Engines handle the actual storage and retrieval of data persisted by a Session. It is probably easiest to think of these as interfaces to a SQL database.

Engines are a very necessary evil and need to be discussed here, but day to day a user of Repose does not need to know too much about how an Engine works, just that it exists. One of the primary reasons for the existence of [Session Factories](ManualSessionFactory.md) is hiding the existence and creation of Repose Engines.

Engines are useful in porting Repose to a framework. For instance, if a framework has an extensive database library  and exposes all of the required database functionality Repose needs, an Engine could be created specifically for that framework so that the existing database handle could be used for Repose as well.

## PDO Engine ##

A PDO based Engine is provided as a part of Repose. It is what will be used by [Configuration Session Factory](ManualConfigurationSessionFactory.md) if not told otherwise.

PDO is preferred as it has a superior method for handling bound query placeholders. No escaping required!


---

[<< Mapping](ManualMapping.md) | **Engine** | [Autoloader >>](ManualAutoloader.md)
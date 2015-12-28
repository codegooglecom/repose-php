# Introduction #

Repose is an Object-Relational Mapping (ORM) library for PHP5. Repose implements a Unit of Work (UoW), an Identity Map and generated proxy classes to provide transparent unobtrusive persistence in PHP.

The only requirement on model classes are that they not be marked as final and for PHP 5.2.x and earlier the persistence aware properties need to be public. There are no framework classes or interfaces that need to be extended or implemented in order to use Repose.

# Features #

  * Flexible default configuration
  * SQL-like query string interface
  * Fluid query interface
  * Class definition simplicity and flexibility
  * Flexible data store engine interface can be extended to work with various data stores
  * Composite primary keys
  * Array collections
  * Many-to-one relationships
  * One-to-many relationships

# Known Issues and Limitations #

Repose PHP ORM is a work in progress. Check out the [Known Issues and Limitations](KnownIssuesAndLimitations.md) of Repose.

# Documentation #

  * [Repose PHP ORM Manual](Manual.md)
  * [Sample Usage](SampleUsage.md)
  * [Sample Code](SampleCode.md)
  * [Sample Queries](SampleQueries.md)
  * [Change Log](ChangeLog.md)
  * _[API Reference](API.md) COMING SOON_

# Support #

For Repose support, please visit the [Repose PHP ORM Forums](http://redmine.dflydev.com/projects/repose/boards).

# Inspiration #

The inspiration for Repose comes from [Hibernate](http://hibernate.org/), [SQLAlchemy](http://www.sqlalchemy.org/) and [Outlet](http://outlet-orm.org/).
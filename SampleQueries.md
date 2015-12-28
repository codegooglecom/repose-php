# Introduction #

The following is a quick sample of using Repose to perform queries. These samples reference the domain model discussed on the [Sample Code](SampleCode.md) page. Please see [Sample Code](SampleCode.md) for information on the domain model used in these samples.

# Table of Contents #



# Status #

The Query interface is still in the design stages. It is possible that the methods to create a Query instance, and how to interact with it, may change drastically in the very near future.

# Examples #

## Get all Bugs ##

```
$bugs = $session->execute('FROM sample_Bug')->all();
```

## Get all Bugs for a specific Project ##

```
$bugs = $session->execute(
    'sample_Bug bug WHERE bug.project.projectId = :projectId',
    array('projectId' => $projectId)
);
```

## Get all Bugs for a specific Project Manager ##

```
$query = $session->query(
    'FROM sample_Bug bug WHERE bug.project.manager.userId = :userId'
);
$bugs = $query->execute(array('userId' => $userId))->all();
```

## Get all Projects for Bugs owned by a specific User ##

```
$query = $session->query(
    'SELECT bug.project FROM sample_Bug bug WHERE bug.owner.userId = :userId'
);
$projects = $query->execute(array('userId' => $userId))->all();
```
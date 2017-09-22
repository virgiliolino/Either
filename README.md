# Exception handling as a form of code micromanagement


I started coding more or less when I was 6 years old with a very primitive version of BASIC. Every line of code needed to be preceded by a line number.
Just like this:
```
10 print "hello"
20 let a = 5
30 for ....
```

Notice the number increasing by 9. This, because I could add something between the lines, I'd have done like this:
```
10 print "hello"
20 let a = 5
30 for ...
15 print "world"
```
then it was enough to write **renum** to have again a distance of 9 between each line and reorder the line number 15 between the 10 and the 20.

To **go to**  a different line we could just write goto and the line number.

After some years it arrived the wonderful Quick Basic. It had two main advantages: the possibility to save to EXE(I didn't called it compilation), and not anymore line numbers! From now on a goto could be referred not anymore to a line number, but to a label. To me it was a wonderful abstraction, instead of writing ```goto 10```, I could just give more sense to my code by doing for example something like ```goto :error```

Nowadays, 50 years after the letter *Go To Statement Considered Harmful* Edsger Dijkstra's, we just stopped using GOTO or at least we think we did. We found many alternatives to do that dirty job in a formally cleaner way. For example we introduced the concept of *Exceptions*. It works wonderfully for operating systems, and should have remained on that lower level, handled by the OS in case of exceptional events not found during the compilation phase, like a division by zero. We mixed that concept with high level concerns to micromanage the execution flow increasing the complexity of the code. We're determining not just how a problem should be solved, but also trying to impose a temporal correlation of every module in the code. Every time it make sense the order of execution of one or more instructions, we're talking about **complexity caused by control**. 

SOLID principles help us here. Design patterns also. Every pattern that group a set of instructions into something else can be considered as a way of reducing complexity caused by control. One pattern that come from the Functional world is the *Either*.

## Either.

An Either is, as the name could suggest, a structure that can be something or something else. Lets consider an example to explain the problem:

We want to query a user by Id from a DB by calling a method *getById*.

```
$repository->getById(5)
```
what if 5 is not existent? Should this method throw an exception? or just return null? I find  both cases a wrong way of describing the problem that lead to a higher complexity. We could instead in a more correct functional way face up reality and determine that actually getById can return two possible types. In case there is a user with id 5, it will return a User Object. Else it will return an Error Value object containing a textual description of the problem.
Such a structure that can be a User or an Error is an Either. An Either will be an Abstract that can be Left or Right. Left is by convention used to describe the kind of structure that represent an Error, Right, instead a correct value.

What follows is an actual piece of code I'm using on my prototype when implementing the code that will be used to handle the repositories.
```
private function queryFetch(PDOStatement $stm): \Utils\Either {
        try {
            $stm->execute();
            $result = $statement->fetch();
            $response = $result ?                
                new \Utils\Right($result) :
                new \Utils\Left("Empty result");
        } catch (Exception $ex) {
            $response = new \Utils\Left(
                "Query error - [{$request}] - {$ex->getMessage()})");
        }
        return $response;
    }
``` 

It become interesting when we introduce a common pattern used to *reduce* an Either. In scala it's called *fold* and can be used also to react to something by executing a piece of code on case of Left, or another piece of code in case of Right.

Using the library in this repository, you will be able to lock down traditional if..then..else or equivalen structure in a more strict pattern, fold.

As an example of use, I will display actual code used in a Scheduler I build to handle execution of jobs. T
```
$processExecutionResult->fold(
            function($processDescriptor) use ($gateway) {
                $gateway->setJobAndPidStatus($processDescriptor[1], TspScheduler::JOB_STATUS_SKIPPED, $processDescriptor[0]);
            },
            function($processDescriptor) use ($gateway) {
                $gateway->setJobAndPidStatus($processDescriptor[1], TspScheduler::JOB_STATUS_FINISHED, $processDescriptor[0]);
            }
        );
```

The result of the job, in the example is assigned to a variable ```$processExecutionResult```. Depending on this result, if its error I will set the job as Skipped, if its successfully executed I will set the state as Finished.

In my code I'm using rarely if...then...else. I have a piece of code for every case, and many of my methods return never two possible values, but one, an Either.

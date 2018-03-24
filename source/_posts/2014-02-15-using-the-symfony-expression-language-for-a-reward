---
title: Using the Symfony Expression Language for a Reward Rules Engine
---
<p>We recently adopted the <a href="http://symfony.com/doc/current/components/expression_language/index.html" target="_blank">Symfony Expression Language</a> in the rules engine at <a href="http://www.opensky.com" target="_blank">OpenSky</a>. It has brought a new level of flexibility to our system and creating new logic has never been easier.</p>

<p>Installing the expression language in your application is easy with composer. Just add the following to your <code>composer.json</code>:</p>

<pre><code>"symfony/expression-language": "2.5.*@dev"
</code></pre>

<p>The expression language allows you to perform expressions that get evaluated with raw PHP code and return a single value. It can be any type of value and is not limited to boolean values. Here is a simple example:</p>

<pre><code>use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$language = new ExpressionLanguage();

$expression = 'user["isActive"] == true and product["price"] &gt; 20';
$context = array(
    'user' =&gt; array(
        'isActive' =&gt; true
    ),
    'product' =&gt; array(
        'price' =&gt; 30
    ),
);

$return = $language-&gt;evaluate($expression, $context);

var_export($return); // true
</code></pre>

<p>That is a very simple example on how to use the raw expression language. Now I will try to demonstrate how you could model a real implementation using <a href="http://www.doctrine-project.org" target="_blank">Doctrine</a> to persist your rules to a database, the <a href="http://symfony.com/doc/current/components/event_dispatcher/introduction.html" target="_blank">Symfony Event Dispatcher</a> to evaluate your rules and execute actions when your expressions evaluate to true.</p>

<p>To get started create a new <code>Rule</code> class and map it to a database using one of the Doctrine object mappers. For this example we will map it using the MongoDB ODM:</p>

<pre><code>use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class Rule
{
    /**
     * @ODM\Id
     */
    private $id;

    /**
     * @ODM\Collection
     */
    private $eventNames = array();

    /**
     * @ODM\String
     */
    private $expression;

    /**
     * @ODM\Collection
     */
    private $actionEvents = array();

    // ...
}
</code></pre>

<p>Now imagine you already have an event named <code>user.add_to_cart</code> being notified in your application. It looks something like this:</p>

<pre><code>use Symfony\Component\EventDispatcher\Event;

class UserAddToCartEvent extends Event
{
    const onUserAddToCart = 'user.add_to_cart';

    private $user;
    private $product;

    // ...
}

class AddToCartController
{
    // ...

    public function addToCartAction($productId)
    {
        // ...

        $this-&gt;dispatcher-&gt;dispatch(UserAddToCartEvent::onUserAddToCart, new UserAddToCartEvent($user, $product));
    }
}
</code></pre>

<p>Say you want to give a reward to users who add items to their cart when they have loved more than <code>20</code> items and the price of the product is greater than <code>50</code> dollars. The <code>Rule</code> model we created earlier allows us to define a rule that will be executed when <code>UserAddToCart::onUserAddToCart</code> is dispatched:</p>

<pre><code>$rule = new Rule();

// set the events this rule should be executed on.
$rule-&gt;setEventNames(array(
    UserAddToCartEvent::onUserAddToCart
));

// set the expression to evaluate when the rule is executed.
// if the user has loved more than 20 items and the price of the product is more than 50 dollars.
// the expression string will be evaluated by the Symfony expression language.
$rule-&gt;setExpression('event.getUser().getNumLoves() &gt; 20 and event.getProduct().getPrice() &gt; 50');

// set the action events to dispatch when the expression evaluates to true.
$rule-&gt;setActionEvents(array(
    array(
        'eventName' =&gt; UserCreditRewardEvent::onUserCreditReward,
        'recipientExpression' =&gt; 'event.getUser()',
        'attributes' =&gt; array(
            'amount' =&gt; 50
        ),
    )
));

$dm-&gt;persist($rule);
$dm-&gt;flush();
</code></pre>

<p>The above example assumes you have a <code>UserCreditRewardEvent</code> setup and a listener setup to process the event to give the user a credit. Here is what the event would look like:</p>

<pre><code>use Symfony\Component\EventDispatcher\Event;

class UserCreditRewardEvent extends Event
{
    const onUserCreditReward = 'user.credit_reward';

    private $user;
    private $amount;

    // ...
}
</code></pre>

<p>And here is what the listener would look like to give the user the credit. This example assumes you already have a <code>CreditManager</code> service with an <code>issueCredit()</code> method you can use to give a user a credit for a dollar amount:</p>

<pre><code>class UserCreditRewardListener
{
    private $creditManager;

    // ...

    public function onUserCreditReward(UserCreditRewardEvent $event)
    {
        $this-&gt;creditManager-&gt;issueCredit(
            $event-&gt;getUser(),
            $event-&gt;getAmount()
        );
    }
}
</code></pre>

<p>Now to bring it all together we need a <code>RuleSubcriber</code> to lookup the <code>Rule</code> objects from the database when events occur in our application. This class will evaluate the rules and then dispatch the resulting action events when the expressions return true.</p>

<pre><code>use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RuleSubcriber implements EventSubscriberInterface
{
    private $dm;
    private $expressionLanguage;
    private $actionEventFactory;

    // ...

    public static function getSubscribedEvents()
    {
        return array(
            UserAddToCartEvent::onUserAddToCart =&gt; array('handleEvent', 0),
        );
    }

    public function handleEvent(Event $event)
    {
        $rules = $this-&gt;findRulesByEventName($event-&gt;getName());

        foreach ($rules as $rule) {
            if ($this-&gt;evaluateRule($rule, $event)) {
                $this-&gt;dispatchActionEvents($rule, $event);
            }
        }
    }

    private function findRulesByEventName($eventName)
    {
        return $this-&gt;dm-&gt;createQueryBuilder()
            -&gt;field('eventNames')-&gt;equals($eventName)
            -&gt;getQuery()
            -&gt;execute();
    }

    private function evaluateRule(Rule $rule, Event $event)
    {
        return $this-&gt;expressionLanguage-&gt;evaluate($rule-&gt;getExpression(), array(
            'event' =&gt; $event,
        ));
    }

    private function dispatchActionEvents(Rule $rule, Event $event)
    {
        foreach ($rule-&gt;getActionEvents() as $action) {
            $this-&gt;dispatchActionEvent($action, $rule, $event);
        }
    }

    private function dispatchActionEvent(array $action, Rule $rule, Event $event)
    {
        $recipientUser = $this-&gt;expressionLanguage($action['recipientExpression'], array(
            'event' =&gt; $event,
        ));

        $actionEvent = $this-&gt;actionEventFactory-&gt;createActionEvent(
            $action,
            $recipientUser,
            $rule
        );

        $this-&gt;dispatcher-&gt;dispatch($action['eventName'], $actionEvent);
    }
}
</code></pre>

<p>The <code>ActionEventFactory</code> used in the above <code>RuleSubcriber</code> is a simple service used to create the action events we dispatch for our rules.</p>

<pre><code>class ActionEventFactory
{
    public function createActionEvent(array $action, User $user, Rule $rule)
    {
        switch ($action['eventName']) {
            // ...

            case UserCreditRewardEvent::onUserCreditReward:
                return new UserCreditRewardEvent($user, $action['attributes']['amount']);
        }
    }
}
</code></pre>

<p>That is it! Now you have the ability to define rules that can be created with a user interface in your application and stored in a database. These rules get evaluated when certain events are dispatched within your application. When those rules evaluate to true you can dispatch other events that can give out credits, give free shipping, send e-mails, or do anything you can possibly imagine. Build up a repository of common actions as events and allow your business people to define new rules and rewards for promotional campaigns without having to involve a software engineer.</p>
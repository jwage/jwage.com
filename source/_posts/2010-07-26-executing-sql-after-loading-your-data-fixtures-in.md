---
title: Executing SQL after loading your data fixtures in symfony 1.4
---
<p>Sometimes you may need to execute some SQL after your data fixtures are loaded in <a href="http://www.symfony-project.org" target="_blank">symfony</a> 1.4 if you need to do something that is specific to your RDBMS that is not supported by <a href="http://www.doctrine-project.org" target="_blank">Doctrine</a> or <a href="http://www.propelorm.org" target="_blank">Propel</a>. Thankfully symfony 1.4 has many well placed events which allow you to hook in to core functionality and execute your own code when certain actions are performed.</p>

<p>Here is an example where you execute some manually SQL statements after the doctrine:data-load task:</p>

<pre><code>class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    // ...
    $this-&gt;dispatcher-&gt;connect('command.post_command', array(
      $this, 'listenToCommandPostCommandEvent'
    ));
  }

  public function listenToCommandPostCommandEvent(sfEvent $event)
  {
    $invoker = $event-&gt;getSubject();
    if ($invoker instanceof sfDoctrineDataLoadTask)
    {
      $conn = Doctrine_Manager::connection();
      $conn-&gt;exec(// ...);
    }
  }
}
</code></pre>

<p>Symfony 1.4 has many events that you can use to customize things when developing your project. <a href="http://www.symfony-project.org/reference/1_4/en/15-Events" target="_blank">Read more</a> about events in the 1.4 documentation.</p>
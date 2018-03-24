---
title: Doctrine DBAL: PHP Database Abstraction Layer
---
<p>Most people think ORM when they hear the name <a href="http://doctrine-project.org" target="_blank">Doctrine</a>, but what most people don&rsquo;t know, or forget, is that Doctrine is built on top of a very powerful Database Abstraction Layer that has been under development for over a decade. It&rsquo;s history can be traced back to 1999 in a library named Metabase which was forked to create PEAR MDB, then MDB2, Zend_DB and finally Doctrine1. In Doctrine2 the DBAL was completely decoupled from the ORM, components re-written for PHP 5.3 and made a standalone library.</p>

<p><strong>What does it support?</strong></p>

<ul><li>Connection Abstraction</li>
<li>Platform Abstraction</li>
<li>Data Type Abstraction</li>
<li>SQL Query Builder</li>
<li>Transactions</li>
<li>Schema Manager</li>
<li>Schema Representation</li>
<li>Events</li>
<li>Prepared Statements</li>
<li>Sharding</li>
</ul><p>Much more&hellip;</p>

<p><strong>Creating a Connection</strong></p>

<p>Creating connections is easy. It can be done by using the <code>DriverManager</code>:</p>

<pre><code>&lt;?php
$config = new \Doctrine\DBAL\Configuration();
//..
$connectionParams = array(
    'dbname' =&gt; 'mydb',
    'user' =&gt; 'user',
    'password' =&gt; 'secret',
    'host' =&gt; 'localhost',
    'driver' =&gt; 'pdo_mysql',
);
$conn = DriverManager::getConnection($connectionParams, $config);
</code></pre>

<p>The <code>DriverManager</code> returns an instance of <code>Doctrine\DBAL\Connection</code> which is a wrapper around the underlying driver connection (which is often a PDO instance).</p>

<p>By default we offer built-in support for many popular relational databases supported by PHP, such as:</p>

<ul><li>pdo_mysql</li>
<li>pdo_sqlite</li>
<li>pdo_pgsql</li>
<li>pdo_oci</li>
<li>pdo_sqlsrv</li>
<li>oci8</li>
</ul><p>If you need to do something custom, don&rsquo;t worry everything is abstracted so you can write your own drivers to communicate with any relational database you want. For example, recently work has <a href="https://github.com/doctrine/dbal/pull/191" target="_blank">begun</a> on integrating <a href="http://www.akiban.com/" target="_blank">Akiban SQL Server</a> with Doctrine.</p>

<p><strong>How to work with your data</strong></p>

<p>The <code>Doctrine\DBAL\Connection</code> object provides a convenient interface for retrieving and manipulating your data. You will find it is familiar and resembles PDO.</p>

<pre><code>$sql = "SELECT * FROM articles";
$stmt = $conn-&gt;query($sql);

while ($row = $stmt-&gt;fetch()) {
    echo $row['headline'];
}
</code></pre>

<p>To send an update and return the affected rows you can do:</p>

<pre><code>$count = $conn-&gt;executeUpdate('UPDATE user SET username = ? WHERE id = ?', array('jwage', 1));
</code></pre>

<p>It also provide a convenient <code>insert()</code> and <code>update()</code> method to make inserting and updating data easier:</p>

<pre><code>$conn-&gt;insert('user', array('username' =&gt; 'jwage'));
// INSERT INTO user (username) VALUES (?) (jwage)

$conn-&gt;update('user', array('username' =&gt; 'jwage'), array('id' =&gt; 1));
// UPDATE user (username) VALUES (?) WHERE id = ? (jwage, 1)
</code></pre>

<p><strong>Fluent Query Builder Interface</strong></p>

<p>If you need a programatic way to build your SQL queries you can do so using the <code>QueryBuilder</code>. The <code>QueryBuilder</code> object has methods to add parts to a SQL statement. The API is roughly the same as that of the DQL Query Builder.</p>

<p>To create a new query builder you can do so from your connection:</p>

<pre><code>$qb = $conn-&gt;createQueryBuilder();
</code></pre>

<p>Now you can start to build your query:</p>

<pre><code>$qb
    -&gt;select('u')
    -&gt;from('users', 'u')
    -&gt;where($qb-&gt;expr()-&gt;eq('u.id', 1));
</code></pre>

<p>You can use named parameters:</p>

<pre><code>$qb = $conn-&gt;createQueryBuilder()
    -&gt;select('u')
    -&gt;from('users', 'u')
    -&gt;where('u.id = :user_id')
    -&gt;setParameter(':user_id', 1);
</code></pre>

<p>It can handle joins:</p>

<pre><code>$qb = $conn-&gt;createQueryBuilder()
    -&gt;select('u.id')
    -&gt;addSelect('p.id')
    -&gt;from('users', 'u')
    -&gt;leftJoin('u', 'phonenumbers', 'u.id = p.user_id');
</code></pre>

<p>Updates and deletes are no problem:</p>

<pre><code>$qb = $conn-&gt;createQueryBuilder()
    -&gt;update('users', 'u')
    -&gt;set('u.password', md5('password'))
    -&gt;where('u.id = ?');

$qb = $conn-&gt;createQueryBuilder()
    -&gt;delete('users', 'u')
    -&gt;where('u.id = :user_id');
    -&gt;setParameter(':user_id', 1);
</code></pre>

<p>If you want to inspect the SQL resulting from a <code>QueryBuilder</code>, that is no problem:</p>

<pre><code>$qb = $em-&gt;createQueryBuilder()
    -&gt;select('u')
    -&gt;from('User', 'u')
echo $qb-&gt;getSQL(); // SELECT u FROM User u
</code></pre>

<p>The interface has much more and handles most everything you can do when writing SQL manually. It instantly makes your queries reusable, extensible and easier to manage.</p>

<p><strong>Managing your Schema</strong></p>

<p>One of my favorite features of the Doctrine 2.x series is the schema management feature. A <code>SchemaManager</code> instance helps you with the abstraction of the generation of SQL assets such as Tables, Sequences, Foreign Keys and Indexes.</p>

<p>To get a <code>SchemaManager</code> you can use the <code>getSchemaManager()</code> method on your connection:</p>

<pre><code>$sm = $conn-&gt;getSchemaManager();
</code></pre>

<p>Now you can introspect your database with the API:</p>

<pre><code>$databases = $sm-&gt;listDatabases();
$sequences = $sm-&gt;listSequences('dbname');

foreach ($sequences as $sequence) {
    echo $sequence-&gt;getName() . "\n";
}
</code></pre>

<p>List the columns in a table:</p>

<pre><code>$columns = $sm-&gt;listTableColumns('user');
foreach ($columns as $column) {
    echo $column-&gt;getName() . ': ' . $column-&gt;getType() . "\n";
}
</code></pre>

<p>You can even issue DDL statements from the <code>SchemaManager</code>:</p>

<pre><code>$table-&gt;addColumn('email_address', 'string');
</code></pre>

<p><strong>Schema Representation</strong></p>

<p>For a complete representation of the current database you can use the createSchema() method which returns an instance of <code>Doctrine\DBAL\Schema\Schema</code>, which you can use in conjunction with the <code>SchemaTool</code> or <code>SchemaComparator</code>.</p>

<pre><code>$fromSchema = $sm-&gt;createSchema();

$toSchema = clone $fromSchema;
$toSchema-&gt;dropTable('user');
$sql = $fromSchema-&gt;getMigrateToSql($toSchema, $conn-&gt;getDatabasePlatform());

print_r($sql);

/*
array(
  0 =&gt; 'DROP TABLE user'
)
*/
</code></pre>

<p>The <code>SchemaManager</code> allows for some nice functionality to be built for the <a href="http://www.doctrine-project.org/projects/orm.html" target="_blank">Doctrine ORM</a> project for reverse engineering databases in to Doctrine mapping files. This makes it easy to get started using the ORM with legacy databases. It is also used in the <a href="http://www.doctrine-project.org/projects/migrations.html" target="_blank">Doctrine Migrations</a> project to allow you to manage versions of your schema and easily deploy changes to production databases in a controlled and versioned fashion.</p>

<p>The next time you need to access a relational database in PHP, whether it be in a proprietary or open source application, consider <a href="http://doctrine-project.org" target="_blank">Doctrine</a>. Take advantage of our community and team of developers so you can focus on your core competency and really excel in it.</p>
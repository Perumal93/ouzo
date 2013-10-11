<?php
use Ouzo\Db\Dialect\PostgresDialect;
use Ouzo\Db\Query;
use Ouzo\Db\QueryType;

class PostgresDialectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PostgresDialect
     */
    private $dialect;

    protected function setUp()
    {
        $this->dialect = new PostgresDialect();
    }

    /**
     * @test
     */
    public function shouldReturnSelectFrom()
    {
        // given
        $query = new Query();
        $query->table = 'products';

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT products.* FROM products AS products', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnDeleteFrom()
    {
        // given
        $query = new Query();
        $query->type = QueryType::$DELETE;
        $query->table = 'products';

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('DELETE FROM products AS products', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnSelectCountFrom()
    {
        // given
        $query = new Query();
        $query->type = QueryType::$COUNT;
        $query->table = 'products';

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT count(*) FROM products AS products', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnSelectColumnsFrom()
    {
        // given
        $query = new Query();
        $query->table = 'products';
        $query->selectColumns = array('id', 'name');

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT id, name FROM products AS products', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnSelectWithSingleOrderBy()
    {
        // given
        $query = new Query();
        $query->table = 'products';
        $query->order = 'id ASC';

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT products.* FROM products AS products ORDER BY id ASC', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnSelectWithMultipleOrderBy()
    {
        // given
        $query = new Query();
        $query->table = 'products';
        $query->order = array('id ASC', 'name DESC');

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT products.* FROM products AS products ORDER BY id ASC, name DESC', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnSelectWithLimit()
    {
        // given
        $query = new Query();
        $query->table = 'products';
        $query->limit = 10;

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT products.* FROM products AS products LIMIT ?', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnSelectWithOffset()
    {
        // given
        $query = new Query();
        $query->table = 'products';
        $query->offset = 10;

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT products.* FROM products AS products OFFSET ?', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnSelectWithSingleWhere()
    {
        // given
        $query = new Query();
        $query->table = 'products';
        $query->where('name = ?');

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT products.* FROM products AS products WHERE name = ?', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnSelectWithMultipleWhere()
    {
        // given
        $query = new Query();
        $query->table = 'products';
        $query->where(array('name' => 'bob', 'id' => '1'));

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT products.* FROM products AS products WHERE name = ? AND id = ?', $sql);
    }

    /**
     * @test
     */
    public function shouldWrapConditionsWithOrInParenthesis()
    {
        // given
        $query = new Query();
        $query->table = 'products';
        $query->where(array('name' => 'bob', 'id' => '1'));
        $query->where('a = 1 OR b = 2');

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT products.* FROM products AS products WHERE name = ? AND id = ? AND (a = 1 OR b = 2)', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnSelectWithWhereIn()
    {
        // given
        $query = new Query();
        $query->table = 'products';
        $query->where(array('name' => array('james', 'bob')));

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT products.* FROM products AS products WHERE name IN (?, ?)', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnSelectWithJoin()
    {
        // given
        $query = new Query();
        $query->table = 'products';
        $query->join('categories', 'id_category', 'id_category', 'category');

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT products.* FROM products AS products LEFT JOIN categories AS categories ON categories.id_category = products.id_category', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnSelectForChainedWhere()
    {
        // given
        $query = new Query();
        $query->table = 'products';
        $query->where(array('name' => 'james'));
        $query->where('id = ?', 1);

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT products.* FROM products AS products WHERE name = ? AND id = ?', $sql);
    }

    /**
     * @test
     */
    public function shouldSkipEmptyWheres()
    {
        // given
        $query = new Query();
        $query->table = 'products';
        $query->where();
        $query->where('id = ?', 1);

        // when
        $sql = $this->dialect->buildQuery($query);

        // then
        $this->assertEquals('SELECT products.* FROM products AS products WHERE id = ?', $sql);
    }

    /**
     * @test
     */
    public function shouldReturnSelectWithMultipleJoins()
    {
        //given
        $query = new Query();
        $query->table = 'products';
        $query
            ->join('categories', 'id_category', 'id_category', 'category')
            ->join('orders', 'id', 'id_product', 'orders')
            ->where('id = ?', 1);

        //when
        $sql = $this->dialect->buildQuery($query);

        //then
        $expected = 'SELECT products.* FROM products AS products LEFT JOIN categories AS categories ON categories.id_category = products.id_category LEFT JOIN orders AS orders ON orders.id = products.id_product WHERE id = ?';
        $this->assertEquals($expected, $sql);
    }
}
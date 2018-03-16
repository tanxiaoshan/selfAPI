<?php

/* 单例工厂类 */
	//通过这个工厂类，可以传递过来一个模型类的类名
	//并返回给该类一个实例（对象），而且保证其为“单例的”
class SingleFactory{
	public static $_allClass = array();//用于存储各个模型类的唯一实例（单例）

	public static function getObject($className){//$model_name是一个模型类的类名
		if( !isset(static::$_allClass[$className]) //如果不存在
			||
		    !(static::$_allClass[$className] instanceof $className)	//不是其实例
		  )
		{
			static::$_allClass[$className] = new $className();
		}
		return static::$_allClass[$className];
	}
}

/*class A{}
var_dump(SingleFactory::getObject('A'));
var_dump(SingleFactory::getObject('A'));*/

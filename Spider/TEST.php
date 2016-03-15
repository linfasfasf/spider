<?php
class Test extends Baseclass{
	public function Test(){
		$this->insertIntoCreUser(111111, 'lin', 'test');
		$this->insertIntoCreUserInfo(111111, 1, '中文', '3500', '2333', '本科', '十年工作经验', 33);
		$this->insertIntoCreUserContent(111111, 1, 'sdf jasdjfoasjdfojasodfjoiasdjfiojsadiofjasiodjfoiasjdfoijasghaslkdhfjkhsakdjfhkasdjhfkashdkfhaskdhfksajdhf', 'sdfsdfsdfsdfsdf');
	}
}
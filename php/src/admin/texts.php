<?php
	if(!defined('dddfd'))
		exit();
	function TextsList() {
		global $pre, $content, $scripturl;
		$q = DBQuery("SELECT Name, Text FROM {$pre}texts", __FILE__, __LINE__);
		$content['texts'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['texts'][] = array(
				'Name' => $row[0],
				'Text' => $row[1],
				'editlink' => $scripturl.'/index.php?action=texts_edit&amp;name='.$row[0]);
		}
		
		TemplateInit('admin');
		TemplateTextsList();
	}
	
	function TextsEdit() {
		global $pre, $content, $scripturl;
		
		if(isset($_POST['submit'])) {
			DBQuery("UPDATE {$pre}texts SET Text='".EscapeDB(Param('text'))."' WHERE Name='".EscapeDB(Param('name'))."'", __FILE__, __LINE__);
		}
		
		if(isset($_POST['preview'])) {
			$content['Preview'] = utf8_encode(Param('text'));
			$content['Text'] = EscapeOU($content['Preview']);
		} else {
			$content['Preview'] = DBQueryOne("SELECT Text FROM {$pre}texts WHERE name='".EscapeDB(Param('name'))."'", __FILE__, __LINE__);
			$content['Text'] = EscapeOU($content['Preview']);
		}
		$content['Name'] = EscapeOU(Param('name'));
		$content['action'] = $scripturl.'/index.php?action=texts_edit';
		
		TemplateInit('admin');
		TemplateTextsEdit();
	}
?>
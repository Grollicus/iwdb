<?php
function TemplateUniMap() {
	global $content, $scripturl;
	
	TemplateHeader();
	TemplateMenu();
	echo '<div class="content">';
	
	echo '<form action="', $scripturl, '/index.php?action=uni_map" method="post">
	<h2>Universum - Karte</h2>
	<table><tr><th colspan="', $content['sysperline'], '">Galaxie <input type="text" size="2" name="gala" value="', $content['gala'],'"/><input type="submit" value="Ok" /></th></tr>';
	foreach($content['galadatalines'] as $line) {
		echo '<tr>';
		foreach($line as $sys) {
			if(!empty($sys))
				echo '<td class="', $sys['age'], '" style="width:25px;"><a href="', $scripturl, '/?action=uni_view&amp;gala_min=', $content['gala'], '&amp;gala_max=', $content['gala'], '&amp;sys_min=', $sys['num'], '&amp;sys_max=', $sys['num'], '">', $sys['num'], '</a></td>';
			else
				echo '<td style="width:25px;">&nbsp;</td>';
		}
		echo '</tr>';
	}
	echo '</table></form>';
	
	echo '</div>';
	TemplateFooter();
}

//NOTICE: Pfeile zum Navigieren durch die Gala, Anzahl der Galaxien 
function TemplateUniWhosIn() {
	global $content, $scripturl;
	
	TemplateHeader();
	TemplateMenu();
	echo '<div class="content">
			<h2>Allianzen in einer Galaxie</h2>
			<form action="', $scripturl, '/?action=uni_whosat" method="post">
			<table>
				<tr><th>Galaxie <input type="text" size="2" name="gal" value="', $content['gal'], '"/><input type="submit" value="Ok" /></th></tr>';
if(isset($content['allys'])) {
	foreach($content['allys'] as $ally)
		echo '	<tr><td>', $ally, '</td></tr>';
}
	echo '	</table>
			</form>
			</div>';
	TemplateFooter();
}

function TemplateUniAllyAt() {
	global $content, $scripturl;
	
	TemplateHeader();
	TemplateMenu();
	echo '<div class="content">
			<h2>Wo ist die Allianz..?</h2>
			<form action="', $scripturl, '/?action=uni_allyat" method="post">
			<table>
				<tr><th>Allytag <input type="text" size="10" name="allytag" value="', $content['allytag'], '"/><input type="submit" value="Ok" /></th></tr>';
if(isset($content['allygals'])) {
	foreach($content['allygals'] as $gal)
		echo '	<tr><td>', $gal, '</td></tr>';
}
	echo '	</table>
			</form>
			</div>';
	TemplateFooter();
}

function TemplateUniAllyOverview() {
	global $content;
	
	TemplateHeader();
	TemplateMenu();
	echo '<div class="content">
			<h2>Welche Ally ist wo?</h2>
			<table>
				<tr><th>Allytag</th><th>Galaxien</th><th>Member</th><th>Planis</th></tr>';
	foreach($content['uniallydata'] as $line) {
		echo '	<tr><td>', $line['ally'], '</td><td>'. $line['galas'], '</td><td>',$line['members'],'</td><td>',$line['planis'],'</td></tr>';
	}
	echo '	</table>
			</div>';
	TemplateFooter();
	
}
?>
<h1>header 1</h1>
<h2>header 2</h2>
<h3>header 3</h3>
<h4>header 4</h4>
<h5>header 5</h5>
<h6>header 6</h6>

<p><a href="">paragraph</a> paragraph paragraph paragraph paragraph paragraph paragraph paragraph paragraph paragraph </p>

<ul>
	<li>listing bullet</li>
    <li>listing bullet</li>
    <li>listing bullet</li>
</ul>

<ol>
	<li>listing bullet</li>
    <li>listing bullet</li>
    <li>listing bullet</li>
</ol>

<table width="50%" border="1" cellpadding="10" cellspacing="1">
    <thead>
        <tr>
            <th>heading 1</th>
            <th>heading 2</th>
            <th>heading 3</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>cell 1</td>
            <td>cell 2</td>
            <td>cell 3</td>
        </tr>
    </tbody>
</table>

<?php
	$x = 5;
	
	while ($x <= 17) {
		echo "<p>Number ".$x."</p>";
		$x++;
	}

?>
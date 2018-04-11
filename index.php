
<?php include 'functions.php' ?>
<!DOCTYPE html>
<html lang="en">
 	<head>
		<link rel="stylesheet" href="//aui-cdn.atlassian.com/aui-adg/5.9.12/css/aui.min.css" media="all">
		<style>
			#dual_x_div{
				width: 526px;
				height: 250px;
				display:	inline-block;
			}
			#dual_x_div > div > div > svg, #dual_y_div > div > div > svg{
				padding:	16px 32px;
				background:	#FFF;
				border-radius:	5px;
			}
			#dual_y_div{
				width: 278px;
				height: 250px;
				display:	inline-block;
			}
			#table_div{
				display:	inline-block;
			}
			#skillMatrix{
				margin-top:	44px;
			}
		</style>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
 	</head>
	<body>
		<section id="content" class="ac-content">
			<div class="aui-page-header">
				<div class="aui-page-header-main">
					<h1>Optimized operations progress summary</h1>
					<div id="dual_x_div"></div>
					<div id="dual_y_div"></div>
					<div id="table_div"></div>
					<div id="skillMatrix"></div>
				</div>
			</div>
		</section>

		<script id="connect-loader" data-options="sizeToParent:true;">
			(function() {
				var getUrlParam = function (param) {
					var codedParam = (new RegExp(param + '=([^&]*)')).exec(window.location.search)[1];
					return decodeURIComponent(codedParam);
				};

				var baseUrl = getUrlParam('xdm_e') + getUrlParam('cp');
				var options = document.getElementById('connect-loader').getAttribute('data-options');

				var script = document.createElement("script");
				script.src = baseUrl + '/atlassian-connect/all.js';

				if(options) {
					script.setAttribute('data-options', options);
				}

				document.getElementsByTagName("head")[0].appendChild(script);
			})();
		</script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['bar', 'table']});
      google.charts.setOnLoadCallback(drawStuff1);
      google.charts.setOnLoadCallback(drawStuff2);
			google.charts.setOnLoadCallback(drawTable);
			google.charts.setOnLoadCallback(skillMatrix);

      function drawStuff1() {
        var data1 = new google.visualization.arrayToDataTable([
          ['', 'Design sprint', 'MVP', 'Sprints', 'Legacy', 'No info'],
          <?php echo '[" ", '.$s_design.', '.$s_mvp.', '.$s_sprint.', '.$s_legacy.', '.$s_noinfo.']' ?>
        ]);

        var options1 = {
          width: 450,
          chart: {
            title: 'General overview'
          },
          bars: 'horizontal'
        };
      var chart1 = new google.charts.Bar(document.getElementById('dual_x_div'));
      chart1.draw(data1, options1);
    	};

      function drawStuff2() {
        var data2 = new google.visualization.arrayToDataTable([
          ['', 'On Time', 'At Risk', 'Late', 'Hold', 'Legacy', 'No info'],
          <?php echo '[" ", '.$f_ontime.', '.$f_atrisk.', '.$f_late.', '.$f_hold.', '.$s_legacy.', '.$f_noinfo.']' ?>
        ]);

        var options2 = {
          width: 220,
          chart: {
            title: 'Times'
          },
          bars: 'vertical'
        };
      var chart2 = new google.charts.Bar(document.getElementById('dual_y_div'));
      chart2.draw(data2, options2);
    	};

			function drawTable() {
				var data = new google.visualization.DataTable();
				data.addColumn('string', 'Project');
				data.addColumn('string', 'Status');
				data.addColumn('string', 'Deadline');
				data.addColumn('string', 'Leader');
				data.addRows([
					<?php echo $datatable ?>
				]);

				var table = new google.visualization.Table(document.getElementById('table_div'));

				table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
			}

			function skillMatrix() {
				var data = new google.visualization.DataTable();
				data.addColumn('string', 'Team Member');
				data.addColumn('string', 'D');
				data.addColumn('string', 'P');
				<?php echo $project_columns ?>
				data.addRows([
					<?php echo $project_dots ?>
				]);

				var table = new google.visualization.Table(document.getElementById('skillMatrix'));

				table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
			}
		</script>
	</body>
</html>
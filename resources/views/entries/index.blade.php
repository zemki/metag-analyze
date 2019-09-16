@extends('layouts.app')

@section('content')
	<div class="columns no-print">
		<div class="column is-half">
			<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
				<ul>
					<li>Metag</li>
					<li><a href="{{url('/')}}">Projects</a></li>
					<li><a href="{{url($case->project->path())}}">{{$case->project->name}}</a></li>
					<li class="is-active" aria-current="page"><a href="#">{{$case->name}}</a></li>
				</ul>
			</nav>
		</div>
	</div>

	<div class="columns no-print">
		<div class="column is-2">
			<div class="mb-3">
				<button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
					onclick="print()"
				>
					Print
				</button>
			</div>
		</div>
		<div class="column is-2">
			<div class="mb-3">
				<button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
					onclick="toggleChart('media')"
				>
					Media
				</button>
			</div>
		</div>
		@foreach($types as $ie)
			<div class="column is-2">
				<div class="mb-3">
					<button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
							onclick="toggleChart('{{preg_replace('/\s+/', '',$ie['name'])}}')"
					>
						{{ucwords($ie['name'])}}
					</button>
				</div>
			</div>
		@endforeach
	</div>

	@if($entriesByMedia !== [])
		<h1 class="title media">Media</h1>
		<timeline :data="{{json_encode($entriesByMedia)}}" class="chart-left media" style="width: 100%;display: show;" :lang="'de'" :discrete="true">
		</timeline>
	@else
		No entries for this study
	@endif

	<div id="graphs" style="height: auto; width: 100%;"></div>

@endsection
@section('pagespecificcss')
	@media print{
	@page {size: landscape}
	.no-print, .no-print *
	{
	display: none !important;
	}
	body {
	margin: 0;
	background-color: #fff;
	padding: 0;
	}
	.chart-left{
	width:100%;
	height:100%;
	page-break-after:always;
	margin-left: -150px;
	}
	.title-chart{
	margin-left: -150px;
	width:100%;
	}
	}
@endsection

@section('pagespecificscripts')

	<script type="text/javascript">

        google.charts.load('current', {'packages': ['timeline']});
        google.charts.setOnLoadCallback(drawChart);

        function toggleChart(c) {
            var x = document.getElementsByClassName(c);
            var i;
            for (i = 0; i < x.length; i++) {
                if (x[i].style.display === "none") {
                    x[i].style.display = "block";
                } else {
                    x[i].style.display = "none";
                }
            }

        }

        function drawChart() {


            /* myChart
			   .data(<myData>)
		   (<myDOMElement>);

		   $inputsEntries EXAMPLE

		   "Value your experience" => 1
		   "name" => "Value your experience"
		   "type" => "scale"
		   "mandatory" => true
		   "numberofanswer" => 0
		   "answers" => array:1 [▶]
		   "begin" => "2019-07-22 13:08:26.411507"
		   "end" => "2019-07-22 16:08:51.166203"
		   */
                <?php
                $realEntry = array();
                ?>

            var entries = [];
            var objectEntry = [];

			@foreach($entriesbyInputs as $e)
            objectEntry.push(<?php echo json_encode($e); ?>);
					@endforeach

            var i = 0;

            _.forEach(objectEntry, function (e) {


                entries[i] = [];
                for (var j = 0; j < e.length; j++) {
                    var entry = [];
                    if(_.isNull(e[j][e[j]['name']])) continue;
					else entry.push(e[j][e[j]['name']].toString());
                    entry.push(e[j]['name']);
                    entry.push('opacity: 1');
                    entry.push(new Date(Date.parse(e[j]['begin'])));
                    entry.push(new Date(Date.parse(e[j]['end'])));
                    entries[i].push(entry);
                }

                // HERE COMPLETE THE CHART FOR SCALE AND ONECHOICE
                if (e[0]['type'] === "scale") entries[i] = completeScaleChart(entries[i],e[0]['begin']);
                if (e[0]['type'] === "one choice") entries[i] = completeOneChoiceChart(entries[i], e[0]['answers'],e[0]['begin']);

                i += 1;
            });

            function completeOneChoiceChart(currentEntries, answers, exampleDate) {

                var included = [];
                for (var i = 0; i < currentEntries.length; i++) included.push(currentEntries[i][0].toString());

                for (var i = 1; i < answers.length; i++) {

                    if (included.indexOf(answers[i].toString()) === -1) {
                        var entry = [];

                        entry.push(answers[i].toString());
                        entry.push('');
                        entry.push('opacity: 0');
                        entry.push(new Date(Date.parse(exampleDate)));
                        entry.push(new Date(Date.parse(exampleDate)));

                        currentEntries.push(entry);
                    }
                }
                return currentEntries;
            }

            function completeScaleChart(currentEntries, exampleDate) {
                console.log(currentEntries);
                var included = [];
                for (var i = 0; i < currentEntries.length; i++) included.push(currentEntries[i][0].toString());

                for (var i = 1; i <= 5; i++) {

                    if (included.indexOf(i.toString()) === -1) {
                        var entry = [];

                        entry.push(i.toString());
                        entry.push('');
                        entry.push('opacity: 0');
                        entry.push(new Date(Date.parse(exampleDate)));
                        entry.push(new Date(Date.parse(exampleDate)));

                        currentEntries.push(entry);
                    }
                }

                currentEntries.sort(function (a, b) {
                    var keyA = a[0],
                        keyB = b[0];
                    // Compare the 2 dates
                    if (keyA < keyB) return -1;
                    if (keyA > keyB) return 1;
                    return 0;
                });

                return currentEntries;
            }

            let options = {
                timeline: {
                    colorByRowLabel: true
                },
				enableInteractivity: true,
				hAxis: {format:'dd.MM hh:MM'},
                explorer: {
                    actions: ['dragToZoom'],
                },


            };

				console.log(entries);
            for (var i = 0; i < entries.length; i++) {

                var div = document.createElement("div");
                var h = document.createElement("H1");
                var t = document.createTextNode(entries[i][0][1]);

                div.style.height = "400px";

				var className = _.find(entries[i],function(x){
				    return x[1] != "";
				});
                div.classList.add(className.toString().split(' ').join(''));
                h.appendChild(t);
                h.classList.add("title");
                h.classList.add(className.toString().split(' ').join(''));
                div.classList.add("chart-left");
                h.classList.add("title-chart");

                document.getElementById("graphs").appendChild(h);
                document.getElementById("graphs").appendChild(div);

                var container = div;
                var chart = new google.visualization.Timeline(container);
                chart.languages = 'de';
                var dataTable = new google.visualization.DataTable();

                dataTable.addColumn({type: 'string', id: 'Role'});
                dataTable.addColumn({type: 'string', id: 'Name'});
                dataTable.addColumn({type: 'string', role: 'style'});
                dataTable.addColumn({type: 'date', id: 'Start'});
                dataTable.addColumn({type: 'date', id: 'End'});


                dataTable.addRows(entries[i]);
                chart.draw(dataTable, options);
            }


        }
	</script>
@endsection

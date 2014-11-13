
var datasets = {}
var graphs = {}

var graph_scale = {}

var water_temperature_graph = {
					type:'line',
					strokeStyle: '#F0F066',
					y: {
							scaleStyle: '#F0F066',
							showGridLines: true,
							labels: true,
							name: "Temperature (C)",
							nameFont: '13px Droid Sans', 
							min: 17,
							max: 27
					},
					x: {
						showGridLines: true,
						gridStyle: '#221010',
							labels: true,
							labelRotation: 45,
							labelFormat: Grapho.formats.time
					},
					data: []
			};
			
var water_level_graph = {
		type:'line',
		strokeStyle: '#6666F0',
		y: {
						axis: 2,
			scaleStyle: '#6666F0',
			showScale: true,
			name: "Level",
			nameFont: '13px Droid Sans',
			labels: true,
			max: 1024,
			min: 0
		},
				data: []
	};
	
var light_level_graph = {
							type:'line',
							strokeStyle: '#F0F066',
							y: {
									scaleStyle: '#F0F066',
									showGridLines: true,
									labels: true,
									name: "Light",
									nameFont: '13px Droid Sans',
									min: 0,
									max: 1024
							},
							x: {
									showGridLines: true,
									gridStyle: '#221010',
									labels: true,
									labelRotation: 45,
									labelFormat: Grapho.formats.time
							},
							data: []
					};

function create_graph(place)
{
	graphs[place] = new Grapho({place: place});
}

function update_graph(graph, dataset, data)
{
	var repeat = false;
	var graph_add;
	
	if(graphs[graph] == undefined)
	{
		console.log("No such graph " + graph);
		return;
	}
		
	if(datasets[dataset] !== undefined)
	{
		graphs[graph].removeDataset(datasets[dataset]);
	}
	
	if(dataset == 'water_temperature')
		graph_add = water_temperature_graph;
	else if(dataset == 'water_level')
		graph_add = water_level_graph;
	else if(dataset == 'light_level')
		graph_add = light_level_graph;
		
	graph_add.data = data;
	
	if(graph_scale[dataset] == undefined)
		graph_scale[dataset] = 0.0;
	
	if(graph_scale[dataset] < 1.0)
	{
		graph_add.data = graph_add.data.map(function(data){ 
							var scaled = graph_add.y.min + (data[1] - graph_add.y.min) * graph_scale[dataset];
							return [ data[0],  scaled ]; 
							});
		graph_scale[dataset] += 0.05;
		repeat = true;
	}
		
	
	datasets[dataset] = graphs[graph].addDataSet(graph_add);
	
	if(repeat)
	{
		setTimeout(function(){update_graph(graph, dataset, data);}, 1);
	}
}

<!doctype html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker3.css" />
	<title>KindPlanner</title>
</head>
<body>
<h1>KindPlanner</h1>
Nieuw kind
<form id="addKind">

	<input name="naam">
	<input id="dob" name="dob">
	<input type="submit">
</form>

<ul id="kinderen"></ul>
<div id="week"></div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/locales/bootstrap-datepicker.nl.min.js"></script>

<script>

	$('#dob').datepicker({
		format: "dd M yyyy",
		startView: 2,
		maxViewMode: 2,
		autoclose: true
	});

	function range(size, startAt = 0) {
		return [...Array(size).keys()].map(i => i + startAt);
	}
</script>

<script>

	class Kind {

		constructor(naam, dob){

			this.naam = naam;
			this.dob = dob;
			this.id = null;
		}

		setId(id){

			this.id = id;
			return this;
		}

		getLeeftijd(datum){

			datum = datum || Date.now();
			let ageDate = new Date(datum - this.dob.getTime());
			return Math.abs(ageDate.getFullYear() - 1970);
		}
	}

	class State {

		constructor(){

			this.kinderen = [];
			this.inc = 0;
			this.selected = {};
		}

		addKind(kind){

			kind.setId(this.inc);
			this.kinderen[this.inc++] = kind;
		}

		select(id, kindid){

			this.selected[id] = kindid;
		}

		rebuild(plain){

			this.inc = plain.inc;
			this.selected = plain.selected || {};

			for(let plainkind of plain.kinderen){

				if(!plainkind) continue;

				let kind = new Kind(plainkind.naam, new Date(plainkind.dob));
				kind.setId(plainkind.id);
				this.kinderen[plainkind.id] = kind;
			}
		}

		getSelected(id){

			return this.selected[id] || undefined;
		}
	}

</script>

<script>

	function displayKinderen(){

		let ul = document.getElementById('kinderen');

		ul.innerHTML = '';

		Current.kinderen.forEach(kind => {

			let li = document.createElement('li');
			li.innerText = `${kind.naam} - ${kind.getLeeftijd()}`;
			let deleteKnop = document.createElement('button');
			deleteKnop.innerText = 'X';
			deleteKnop.addEventListener('click', () => {

				delete Current.kinderen[kind.id];
				store();
				displayKinderen();
				displayTables();
			});
			li.appendChild(deleteKnop);
			ul.appendChild(li);
		});
	}

</script>
<script>

	let Current = new State();

	function store(){

		localStorage.setItem('current', JSON.stringify(Current));
	}

	function restore(){

		let stored = JSON.parse(localStorage.getItem('current'));

		if(stored){

			Current.rebuild(stored);
		}
	}

	restore();
	displayKinderen();

</script>

<script>

	let form = document.getElementById('addKind');
	form.addEventListener('submit', event => {

		event.preventDefault();

		let kind = new Kind(form.naam.value, $('#dob').datepicker('getDate'));
		Current.addKind(kind);
		console.log(kind.naam, kind.getLeeftijd());
		store();
		displayKinderen();
		displayTables();
	});
</script>

<script>

	const dagen = ['Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag'];
	const uren = range(12, 7);
	const leeftijden = ['0-0','0-1','1-0','1-1','2-0','4-0'];
	const week = document.getElementById('week');

	function blankOption(){
		let blank = document.createElement("option");
		blank.text = '';
		return blank;
	}

	function kindOption(kind, selected){
		let option = document.createElement("option");
		option.value = kind.id;
		option.text = kind.naam;
		option.selected = selected;
		return option;
	}

	function createKindSelect(kinderen, id){

		let select = document.createElement('select');
		select.id = id;
		select.add(blankOption(), null);
		select.addEventListener('change', event => {

			Current.select(event.target.id, event.target.value);
			store();
		});
		kinderen.forEach(kind => select.add(kindOption(kind, kind.id == Current.getSelected(id))));
		return select;
	}

	function displayTables(){

		week.innerHTML = '';

		dagen.forEach(dag => {

			let table = document.createElement('table');
			let tr = document.createElement('tr');

			[dag, ''].forEach(text => {

				let th = document.createElement('th');
				th.innerText = text;
				tr.appendChild(th);
			});

			leeftijden.forEach(text => {

				let th = document.createElement('th');
				th.innerText = `vanaf ${text[0]}`;
				tr.appendChild(th);
			});

			table.appendChild(tr);
			week.appendChild(table);

			uren.forEach(uur => {
				let tr = document.createElement('tr');
				tr.insertCell().appendChild(document.createTextNode(dag));
				let begin = uur.toString().padStart(2, '0');
				let eind = (uur + 1).toString().padStart(2, '0');
				tr.insertCell().appendChild(document.createTextNode(`${begin}-${eind}`));

				table.appendChild(tr);

				leeftijden.forEach(leeftijd => {

					tr.insertCell().appendChild(createKindSelect(Current.kinderen.filter(kind => kind.getLeeftijd() >= leeftijd[0]), `${dag}-${uur}-${leeftijd}`));
				});
			});
		});
	}

	displayTables();
</script>

</body>
</html>
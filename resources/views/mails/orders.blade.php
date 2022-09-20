@extends('mails.layouts')

@section('style')
	<style>
		
	</style>
@endsection

@section('content')
<div class="container">
	<div class="card" style="width: 100%;">
		<div class="card-header text-center p-3">
			Featured
		</div>

		<div>
			Bonjour (nom utilisateur)
		</div>

		<div>
			POur information - nous avons recus votre commande n : ... elle est maintent en cours de traitement
		</div>

		<div>
			Numero de commande : 143
		</div>

		<div>
			date de commande : 20-09-12
		</div>

		<div>
			Choix de livraison : a livrer
		</div>

		<table class="table table-hover">
			<thead>
				<tr>
					<th scope="col">#</th>
					<th scope="col">First</th>
					<th scope="col">Last</th>
					<th scope="col">Handle</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th scope="row">1</th>
					<td>Mark</td>
					<td>Otto</td>
					<td>@mdo</td>
				</tr>
				<tr>
					<th scope="row">2</th>
					<td>Jacob</td>
					<td>Thornton</td>
					<td>@fat</td>
				</tr>
				<tr>
					<th scope="row">3</th>
					<td colspan="2">Larry the Bird</td>
					<td>@twitter</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div>
		<div>Address de livraison</div> 
		<div>Lorem, ipsum.</div>
		<div>Lorem ipsum dolor, sit amet consectetur </div>
		<div>Lorem, ipsum.</div>
		<div>Lorem ipsum dolor sit.</div>
	</div>
</div>
	

@endsection
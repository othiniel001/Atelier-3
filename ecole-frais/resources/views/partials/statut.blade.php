@php($couleurs = ['en_attente' => 'warning text-dark', 'valide' => 'success', 'rejete' => 'danger'])
<span class="badge bg-{{ $couleurs[$paiement->statut] ?? 'secondary' }}">{{ $paiement->libelle_statut }}</span>

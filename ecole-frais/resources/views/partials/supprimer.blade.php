<form method="POST" action="{{ $action }}" class="d-inline" onsubmit="return confirm('{{ $confirmation ?? 'Confirmer la suppression ?' }}')">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger">{{ $libelle ?? 'Supprimer' }}</button>
</form>

import java.time.LocalDate;

public class AccessoireElectronique {
    String typeAccessoire;
    String compatibilite;
    int longueurCable;
    String materiau;
    int capacite;
    public AccessoireElectronique(String codeProduit, String nomProduit, String marqueProduit, int quantiteEnStock, double prixProduit, int garantieProduit, LocalDate dateproduit, String typeAccessoire, String compatibilite, int l
    , String materiau, int capacite){
        this.typeAccessoire=typeAccessoire;
        this.compatibilite=compatibilite;
        this.longueurCable=longueurCable;
        this.materiau=materiau;
        this.capacite=capacite;
    }
    public void testCompatibilite(){
        System.out.println("type d'accessoire :"+typeAccessoire);
        System.out.println("compatible avec :"+compatibilite);
        System.out.println("le cable mesure :"+longueurCable);
        System.out.println("le materiau est :"+materiau);
        System.out.println("la capacite est de:"+capacite);
    }
}

import java.time.LocalDate;

public class EquipementInformatique {
    String Rom;
    String Ram;
    String Processeur;
    String typeEcran;
    String systeme;
    public EquipementInformatique(String codeProduit, String nomProduit, String marqueProduit, int quantiteEnStock, double prixProduit, int garantieProduit, LocalDate dateproduit, String rom, String ram, String processeur, String typeEcran, String systeme){
        this.Rom=Rom;
        this.Ram=Ram;
        this.Processeur=processeur;
        this.typeEcran=typeEcran;
        this.systeme=systeme;
    }
    public void AfficherSpecs() {
        System.out.println("la rom est de:"+Rom);
        System.out.println("la ram est de :"+Ram);
        System.out.println("le processeur est de :" + Processeur );
        System.out.println("l'ecran est :"+typeEcran);
        System.out.println("le systeme est :" +systeme);
    }
}

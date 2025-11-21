import java.time.LocalDate;
import java.util.Date;
public class ProduitElectronique{
    public String codeProduit;
    public String nomProduit;
    public String marqueProduit;
    public int quantiteEnStock;
    public Double prixProduit;
    public  int garantieProduit;
    public LocalDate dateproduit;
    public ProduitElectronique(String codeProduit, String nomProduit, String marqueProduit, int quantiteEnStock, double prixProduit, int garantieProduit, LocalDate dateproduit ){
        this.codeProduit=codeProduit;
        this.nomProduit=nomProduit;
        this.marqueProduit=marqueProduit;
        this.quantiteEnStock=quantiteEnStock;
        this.prixProduit=prixProduit;
        this.garantieProduit=garantieProduit;
        this.dateproduit=dateproduit;
    }
    public String getCodeProduit(){
        return codeProduit;
    }
    public String setcodeProduit(){
        return codeProduit;
    }

    public String getNomProduitomProduit() {
        return nomProduit;
    }
    public String setnomproduit(){
        return nomProduit;
    }
    public String getMarqueProduit(){
        return marqueProduit;
    }
    public String setmarqueProduit(){
        return marqueProduit;
    }

    public int getQuantiteEnStock() {
        return quantiteEnStock;
    }
    public int setquantiteEnStock(){
        return quantiteEnStock;
    }

    public Double getPrixProduit() {
        return prixProduit;
    }
    public double setprixProduit(){
        return prixProduit;
    }

    public int getGarantieProduit() {
        return garantieProduit;
    }
    public int setgarantieProduit(){
        return garantieProduit;
    }

    public void MiseAJourStock(int quantite){
        this.quantiteEnStock+=quantite;
    }
    public void AfficherStock(){
        System.out.println("code:"+codeProduit);
        System.out.println("nom du produit:"+nomProduit);
        System.out.println("marque du produit:"+marqueProduit);
        System.out.println("quantite du produit:"+quantiteEnStock);
        System.out.println("prix du produit:"+prixProduit);
        System.out.println("la garantie du produit:"+garantieProduit);
        System.out.println("date du produit:"+dateproduit);
    }
}

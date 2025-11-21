import java.time.LocalDate;

public class Electromenager {
    int puissanceW;
    int Poids;
    int capacite;
    int dimension;
    double consommation;
    public Electromenager(String codeProduit, String nomProduit, String marqueProduit, int quantiteEnStock, double prixProduit, int garantieProduit, LocalDate dateproduit, int puissanceW, int poids, int capacite, int dimension, double consommation){

        this.puissanceW=puissanceW;
        this.Poids=poids;
        this.capacite=capacite;
        this.dimension=dimension;
        this.consommation=consommation;
    }
    public void InfoEnergie(){
        System.out.println("la puissance est de: "+puissanceW);
        System.out.println("le poids est de : "+Poids);
        System.out.println("la capacite est de : "+capacite);
        System.out.println("la dimension est de :"+ dimension);
        System.out.println("la consommmasion est de :"+consommation);
    }
}

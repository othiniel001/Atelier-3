import java.time.LocalDate;
import java.util.ArrayList;

public class Main {
    public static void main(String[]args){
        ArrayList<ProduitElectronique>stock=new ArrayList<>();
        Electromenager p1=new Electromenager(
                "ev7",
                "ventilateur",
                "samsung",
                5,
                100,
                24,
                LocalDate.of(2024,06,30),
                50,
                60,
                2,
                4,
                7.6
        );
        EquipementInformatique p2=new EquipementInformatique(
                "ipv4",
                "laptop",
                "dell",
                1,
                120,
                12,
                LocalDate.of(2024,7,4),
                "256",
                "16",
                "2.50ghzt",
                "normale",
                "windows"
        );
        AccessoireElectronique p3=new AccessoireElectronique(
               "EC4",
               "CHARGEUR",
                "SONY",
                4,
                4655,
                12,
                LocalDate.of(2024,11,7),
                "avec machine de marque",
                "4",
                6,
                "atp",
                7
        );
        p1.InfoEnergie();
        p2.AfficherSpecs();
        p3.testCompatibilite();
    }

}

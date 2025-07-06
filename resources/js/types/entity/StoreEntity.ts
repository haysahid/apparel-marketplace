interface StoreEntity {
    id: number;
    name: string;
    description: string;
    address: string;
    phone: string;
    logo: string;
    banner: string;
    rajaongkir_origin_id: number | null;
    rajaongkir_origin_label: string | null;
    province_name: string | null;
    city_name: string | null;
    district_name: string | null;
    subdistrict_name: string | null;
    zip_code: string | null;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;

    // Relationships
    advantages: StoreAdvantageEntity[];
    certificates: string[];
    social_links: StoreSocialLinkEntity[];
}

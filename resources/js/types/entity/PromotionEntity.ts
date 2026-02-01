interface PromotionEntity {
    id: number;
    store_id: number | null;
    name: string;
    slug: string;
    description: string | null;
    image: string;
    redirection_url: string;
    start_date: string;
    end_date: string;
}

<script setup>
import PostItem from "@/Components/app/PostItem.vue";

const post1 = {
    user: {
        id: 1,
        avatar: 'https://randomuser.me/api/portraits/women/50.jpg',
        name: 'Elvire Dev'
    },
    group: null,
    attachments: [
        {
            id: 1,
            name: 'test.png',
            url: 'https://picsum.photos/1000',
            mime: 'image/png'
        },
        {
            id: 2,
            name: 'test2.png',
            url: 'https://picsum.photos/1000',
            mime: 'image/png'
        },
        {
            id: 3,
            name: 'MyDocument.docx',
            url: '#',
            mime: 'application/msword'
        }
    ],
    body: `
      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dolore odio, provident. Cupiditate eius enim itaque laboriosam minus modi temporibus tenetur? Eveniet iste minus placeat quasi.</p>

      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Atque beatae cupiditate deleniti deserunt facere labore maxime optio placeat reiciendis veritatis! Adipisci architecto enim excepturi molestiae recusandae reprehenderit temporibus vel veritatis.</p>
    `,
    created_at: '2023-11-19 15:12'
}

const post2 = {
    user: {
        id: 2,
        avatar: 'https://randomuser.me/api/portraits/men/40.jpg',
        name: 'James Doe'
    },
    group: {
        id: 1,
        name: 'Laravel Developers'
    },
    body: `
      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit.</p>
    `,
    created_at: '2023-11-19 15:12'
}
</script>

<template>
    <div class="overflow-auto">
        <PostItem :post="post1" />
        <PostItem :post="post2" />
    </div>
</template>

<style scoped>

</style>

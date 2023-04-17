<template>
    <div>
        {{ value }}
    </div>
</template>

<script>
export default {
    inheritAttrs: false,
    name: "input-string",
    props: {
        value: [String, Number],
    },
}
</script>

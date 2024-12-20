<template>
<div>
    <h1>Panier</h1>
    <table class="table">
      <thead>
        <tr>
          <th>Produit</th>
          <th>Prix</th>
          <th>Quantité</th>
          <th>Total</th>
          <th>Action</th>
          <th>Supprimer</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in cartItems" :key="item.product.id">
          <td>{{ item.product.name }}</td>
          <td>{{ item.product.price }} €</td>
          <td>{{ item.quantity }}</td>
          <td>{{ item.total }} €</td>
          <td>
            <button @click="addItem(item.product.id)" class="btn btn-success">+</button>
            <button @click="decreaseItem(item.product.id)" class="btn btn-warning">-</button>
          </td>
          <td>
            <button @click="removeItem(item.product.id)" class="btn btn-danger">Supprimer</button>
          </td>
        </tr>
        <tr>
          <td colspan="3">Total</td>
          <td>{{ cartTotal }} €</td>
          <td colspan="2">
            <button @click="emptyCart" class="btn btn-danger">Vider le panier</button>
          </td>
        </tr>
      </tbody>
    </table>
    <a v-if="cartItems.length > 0" @click="placeOrder" class="order-link">Commander</a>
  </div>
</template>

<script setup>
import { ref } from "vue";

const cartItems = ref([])
</script>

<style>
h1:hover{
color: red;
}
</style> 
